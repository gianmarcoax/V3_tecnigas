<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCleanlinessConfig extends Model
{
    protected $table = 'order_cleanliness_configs';

    protected $fillable = [
        'score_thresholds',
        'discount_rules',
    ];

    protected $casts = [
        'score_thresholds' => 'array',
        'discount_rules'   => 'array',
    ];

    /**
     * Returns the singleton config record, creating it with defaults if needed.
     */
    public static function current(): static
    {
        $cfg = static::first();
        if (!$cfg) {
            $cfg = static::create([
                'score_thresholds' => [
                    ['from' => 0,   'to' => 0.5, 'points' => 0],
                    ['from' => 0.5, 'to' => 1.5, 'points' => 1],
                    ['from' => 1.5, 'to' => 2.0, 'points' => 2],
                ],
                'discount_rules' => [
                    ['points' => 0, 'discount_pct' => 100],
                    ['points' => 1, 'discount_pct' => 50],
                    ['points' => 2, 'discount_pct' => 0],
                ],
            ]);
        }
        return $cfg;
    }

    /**
     * Resolves a raw average (float) to integer points (0, 1 or 2)
     * using the configured score_thresholds.
     */
    public function resolvePoints(float $avg): int
    {
        $rounded = round($avg, 1);
        $points  = 0;
        foreach ($this->score_thresholds as $t) {
            if ($rounded >= floatval($t['from']) && $rounded <= floatval($t['to'])) {
                $points = (int)$t['points'];
            }
        }
        return $points;
    }

    /**
     * Returns the discount percentage (0–100) for a given points value.
     */
    public function discountPct(int $points): int
    {
        foreach ($this->discount_rules as $r) {
            if ((int)$r['points'] === $points) {
                return (int)$r['discount_pct'];
            }
        }
        return 0;
    }
}
