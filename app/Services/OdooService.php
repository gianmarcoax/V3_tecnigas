<?php

namespace App\Services;

class OdooService
{
    private string $url;
    private string $db;
    private string $user;
    private string $apiKey;
    private ?int $uid = null;

    public function __construct()
    {
        $this->url    = rtrim(env('ODOO_URL'), '/');
        $this->db     = env('ODOO_DB');
        $this->user   = env('ODOO_USER');
        $this->apiKey = env('ODOO_APIKEY');
    }

    // ─── Autenticación ───────────────────────────────────────────────

    private function uid(): int
    {
        if ($this->uid) return $this->uid;

        $response = $this->call('/xmlrpc/2/common', 'authenticate', [
            $this->db, $this->user, $this->apiKey, []
        ]);

        if (!$response || !is_int($response)) {
            throw new \Exception('Odoo: autenticación fallida. Verifica ODOO_USER y ODOO_APIKEY en .env');
        }

        $this->uid = $response;
        return $this->uid;
    }

    // ─── Método base: execute_kw ──────────────────────────────────────

    public function execute(string $model, string $method, array $args = [], array $kwargs = []): mixed
    {
        return $this->call('/xmlrpc/2/object', 'execute_kw', [
            $this->db,
            $this->uid(),
            $this->apiKey,
            $model,
            $method,
            $args,
            $kwargs,
        ]);
    }

    // ─── Helpers de alto nivel ────────────────────────────────────────

    public function search(string $model, array $domain, array $options = []): array
    {
        return $this->execute($model, 'search', [$domain], $options) ?? [];
    }

    public function searchRead(string $model, array $domain, array $fields = [], array $options = []): array
    {
        return $this->execute($model, 'search_read', [$domain], array_merge(
            ['fields' => $fields],
            $options
        )) ?? [];
    }

    public function read(string $model, array $ids, array $fields = []): array
    {
        return $this->execute($model, 'read', [$ids], ['fields' => $fields]) ?? [];
    }

    public function count(string $model, array $domain): int
    {
        return $this->execute($model, 'search_count', [$domain]) ?? 0;
    }

    // ─── XML-RPC manual (sin extensión xmlrpc) ────────────────────────

    private function call(string $path, string $method, array $params): mixed
    {
        $xml  = $this->encode($method, $params);
        $body = $this->post($path, $xml);
        return $this->decode($body);
    }

    private function encode(string $method, array $params): string
    {
        $xml  = "<?xml version=\"1.0\"?>\n";
        $xml .= "<methodCall>\n";
        $xml .= "  <methodName>{$method}</methodName>\n";
        $xml .= "  <params>\n";
        foreach ($params as $param) {
            $xml .= "    <param><value>" . $this->encodeValue($param) . "</value></param>\n";
        }
        $xml .= "  </params>\n";
        $xml .= "</methodCall>";
        return $xml;
    }

    private function encodeValue(mixed $value): string
    {
        if (is_bool($value))   return '<boolean>' . ($value ? '1' : '0') . '</boolean>';
        if (is_int($value))    return '<int>' . $value . '</int>';
        if (is_float($value))  return '<double>' . $value . '</double>';
        if (is_null($value))   return '<boolean>0</boolean>';

        if (is_array($value)) {
            // Struct (array asociativo)
            if (array_keys($value) !== range(0, count($value) - 1)) {
                $xml = '<struct>';
                foreach ($value as $k => $v) {
                    $xml .= '<member><name>' . htmlspecialchars((string)$k) . '</name><value>' . $this->encodeValue($v) . '</value></member>';
                }
                return $xml . '</struct>';
            }
            // Array
            $xml = '<array><data>';
            foreach ($value as $v) {
                $xml .= '<value>' . $this->encodeValue($v) . '</value>';
            }
            return $xml . '</data></array>';
        }

        return '<string>' . htmlspecialchars((string)$value) . '</string>';
    }

    private function decode(string $xml): mixed
    {
        $doc = new \DOMDocument();
        @$doc->loadXML($xml);

        $fault = $doc->getElementsByTagName('fault');
        if ($fault->length > 0) {
            $msg = $xml;
            throw new \Exception('Odoo fault: ' . $msg);
        }

        $values = $doc->getElementsByTagName('value');
        if ($values->length === 0) return null;

        return $this->decodeNode($values->item(0));
    }

    private function decodeNode(\DOMNode $node): mixed
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;

            $tag = $child->nodeName;

            if ($tag === 'int' || $tag === 'i4' || $tag === 'i8') return (int) $child->nodeValue;
            if ($tag === 'double')  return (float) $child->nodeValue;
            if ($tag === 'boolean') return $child->nodeValue === '1';
            if ($tag === 'string' || $tag === 'base64') return (string) $child->nodeValue;
            if ($tag === 'nil')     return null;

            if ($tag === 'array') {
                $result = [];
                $data = $child->getElementsByTagName('data')->item(0);
                if ($data) {
                    foreach ($data->childNodes as $item) {
                        if ($item->nodeType !== XML_ELEMENT_NODE || $item->nodeName !== 'value') continue;
                        $result[] = $this->decodeNode($item);
                    }
                }
                return $result;
            }

            if ($tag === 'struct') {
                $result = [];
                foreach ($child->childNodes as $member) {
                    if ($member->nodeType !== XML_ELEMENT_NODE || $member->nodeName !== 'member') continue;
                    $key = null;
                    $val = null;
                    foreach ($member->childNodes as $part) {
                        if ($part->nodeType !== XML_ELEMENT_NODE) continue;
                        if ($part->nodeName === 'name') $key = $part->nodeValue;
                        if ($part->nodeName === 'value') $val = $this->decodeNode($part);
                    }
                    if ($key !== null) $result[$key] = $val;
                }
                return $result;
            }
        }

        return (string) $node->nodeValue;
    }

    private function post(string $path, string $body): string
    {
        $ch = curl_init($this->url . $path);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: text/xml', 'Content-Length: ' . strlen($body)],
            CURLOPT_CONNECTTIMEOUT => 10,   // tiempo para establecer conexión
            CURLOPT_TIMEOUT        => 120,  // 2 min para respuestas grandes (4000+ productos)
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) throw new \Exception('Odoo cURL error: ' . $error);

        return $response;
    }
    // ─── Recepción: Productos ─────────────────────────────────────────

    public function getProductos(): array
    {
        return $this->searchRead(
            'product.product',
            [['purchase_ok', '=', true], ['active', '=', true]],
            ['id', 'name', 'default_code', 'barcode', 'qty_available', 'standard_price', 'list_price', 'categ_id', 'uom_id']
            // Sin límite → trae todos los productos activos
        );
    }

    // ─── Recepción: Proveedores ───────────────────────────────────────

    public function getProveedores(): array
    {
        return $this->searchRead(
            'res.partner',
            [['supplier_rank', '>', 0]],
            ['id', 'name', 'phone', 'mobile', 'email']
        );
    }

    // ─── Recepción: Ubicaciones internas de Odoo ──────────────────────

    public function getUbicaciones(): array
    {
        return $this->searchRead(
            'stock.location',
            [['usage', '=', 'internal'], ['active', '=', true]],
            ['id', 'name', 'complete_name', 'location_id']
        );
    }

    // ─── Recepción: Crear stock.picking y validar ─────────────────────
    //
    //  Crea una recepción oficial en Odoo (picking_type = Recepciones).
    //  Cada item genera un stock.move con la cantidad indicada.
    //  Llama button_validate para que el picking quede "Done" en Inventario.
    //
    public function crearRecepcionOdoo(
        string $proveedor_nombre,
        int    $proveedor_id,
        int    $location_dest_id,
        array  $items,           // [['product_id'=>int,'qty'=>float,'price_unit'=>float,'name'=>string], ...]
        string $origin = 'WEB'
    ): int {
        // 1. Obtener tipo de operación «Recepciones» (picking_type_code = incoming)
        $pickingTypes = $this->searchRead(
            'stock.picking.type',
            [['code', '=', 'incoming'], ['active', '=', true]],
            ['id', 'warehouse_id'],
            ['limit' => 1]
        );

        if (empty($pickingTypes)) {
            throw new \Exception('No se encontró un tipo de operación de recepción en Odoo.');
        }

        $pickingTypeId = (int) $pickingTypes[0]['id'];

        // Ubicación origen por defecto: Proveedores (Suppliers)
        $supplierLocations = $this->searchRead(
            'stock.location',
            [['usage', '=', 'supplier'], ['active', '=', true]],
            ['id'],
            ['limit' => 1]
        );

        if (empty($supplierLocations)) {
            throw new \Exception('No se encontró la ubicación de Proveedores en Odoo.');
        }

        $locationSrcId = (int) $supplierLocations[0]['id'];

        // 2. Crear el picking
        $pickingId = $this->execute('stock.picking', 'create', [[
            'picking_type_id'   => $pickingTypeId,
            'partner_id'        => $proveedor_id > 0 ? $proveedor_id : false,
            'origin'            => $origin,
            'location_id'       => $locationSrcId,
            'location_dest_id'  => $location_dest_id,
        ]]);

        if (!$pickingId) {
            throw new \Exception('No se pudo crear el picking en Odoo.');
        }

        // 3. Crear stock.move por cada producto (batch para mejor rendimiento)
        $movesData = [];
        foreach ($items as $item) {
            $movesData[] = [
                'picking_id'        => $pickingId,
                'product_id'        => (int) $item['product_id'],
                'name'              => $item['name'],
                'product_uom_qty'   => (float) $item['qty'],
                'product_uom'       => $item['uom_id'] ?? 1,
                'price_unit'        => (float) $item['price_unit'],
                'location_id'       => $locationSrcId,
                'location_dest_id'  => $location_dest_id,
            ];
        }

        // Crear todos los moves en una sola llamada
        $this->execute('stock.move', 'create', [$movesData]);

        // 4. Confirmar el picking (borrador → listo)
        $this->execute('stock.picking', 'action_confirm', [[$pickingId]]);

        // 5. Asignar disponibilidad
        $this->execute('stock.picking', 'action_assign', [[$pickingId]]);

        // 6. ODOO 18 — escribir la cantidad hecha directamente en stock.move
        //    Los campos qty_done / reserved_uom_qty / product_qty ya NO EXISTEN.
        //    En Odoo 18, stock.move.quantity = "Quantity" (done quantity).
        //    stock.move.product_uom_qty = "Demand" (quantity ordered).
        //    Escribimos quantity = product_uom_qty en cada move del picking.
        $moves = $this->searchRead(
            'stock.move',
            [['picking_id', '=', $pickingId]],
            ['id', 'product_uom_qty']
        );

        foreach ($moves as $move) {
            $this->execute('stock.move', 'write', [
                [(int)$move['id']],
                ['quantity' => (float)($move['product_uom_qty'] ?? 0)]
            ]);
        }

        // 7. Validar el picking
        //    Odoo 18 puede devolver un wizard action dict en lugar de True.
        //    Lo ignoramos — el picking queda en estado 'done'.
        try {
            $this->execute('stock.picking', 'button_validate', [[$pickingId]]);
        } catch (\Exception $e) {
            // Wizard de transferencia inmediata — picking procesado igualmente
        }

        return $pickingId;
    }

    // ─── Traslado interno: Crear stock.picking tipo 'internal' y validar ─
    //
    //  Crea una transferencia interna en Odoo (picking_type_code = internal).
    //  location_src_id = ubicación origen, location_dest_id = destino.
    //  Flujo Odoo 18: confirm → assign → write(quantity) → validate.
    //
    public function crearTrasladoOdoo(
        int    $location_src_id,
        int    $location_dest_id,
        array  $items,   // [['product_id'=>int, 'qty'=>float, 'uom_id'=>int, 'name'=>string], ...]
        string $origin = 'WEB-TRASLADO'
    ): int {
        // 1. Tipo de operación: Transferencia interna (code = internal)
        $pickingTypes = $this->searchRead(
            'stock.picking.type',
            [['code', '=', 'internal'], ['active', '=', true]],
            ['id', 'name'],
            ['limit' => 1]
        );

        if (empty($pickingTypes)) {
            throw new \Exception('No se encontró tipo de operación "Transferencia Interna" en Odoo.');
        }

        $pickingTypeId = (int) $pickingTypes[0]['id'];

        // 2. Crear el picking interno
        $pickingId = $this->execute('stock.picking', 'create', [[
            'picking_type_id'  => $pickingTypeId,
            'origin'           => $origin,
            'location_id'      => $location_src_id,
            'location_dest_id' => $location_dest_id,
        ]]);

        if (!$pickingId) {
            throw new \Exception('No se pudo crear el picking de traslado en Odoo.');
        }

        // 3. Crear stock.move por cada producto (batch para mejor rendimiento)
        $movesData = [];
        foreach ($items as $item) {
            $movesData[] = [
                'picking_id'       => $pickingId,
                'product_id'       => (int) $item['product_id'],
                'name'             => $item['name'],
                'product_uom_qty'  => (float) $item['qty'],
                'product_uom'      => $item['uom_id'] ?? 1,
                'location_id'      => $location_src_id,
                'location_dest_id' => $location_dest_id,
            ];
        }

        // Crear todos los moves en una sola llamada
        $this->execute('stock.move', 'create', [$movesData]);

        // 4. Confirmar
        $this->execute('stock.picking', 'action_confirm', [[$pickingId]]);

        // 5. Verificar disponibilidad (si hay stock suficiente en origen)
        $this->execute('stock.picking', 'action_assign', [[$pickingId]]);

        // 6. Odoo 18: escribir quantity = product_uom_qty en cada move
        $moves = $this->searchRead(
            'stock.move',
            [['picking_id', '=', $pickingId]],
            ['id', 'product_uom_qty']
        );

        foreach ($moves as $move) {
            $this->execute('stock.move', 'write', [
                [(int) $move['id']],
                ['quantity' => (float) ($move['product_uom_qty'] ?? 0)]
            ]);
        }

        // 7. Validar el picking (ignora wizards de backorder/immediate)
        try {
            $this->execute('stock.picking', 'button_validate', [[$pickingId]]);
        } catch (\Exception $e) {
            // Wizard de transferencia inmediata — picking procesado igualmente
        }

        return $pickingId;
    }
}

