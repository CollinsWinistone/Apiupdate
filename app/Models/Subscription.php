<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;

    protected $table = 'subscriptions';

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'currency_prices' => 'array'
    ];

    public function getCurrency()
    {
        return Currency::getCurrency();
    }

    public function getPrice($period)
    {
        $price      = $this->price_monthly;
        $period     = $period == 'year' ? 'yearly' : 'monthly';
        $currency   = $this->getCurrency();

        if ($period == 'yearly') {
            $price = $this->price_yearly;
        }

        $prices = $this->getPrices();
        if (isset($prices[$period][$currency])) {
            return $prices[$period][$currency];
        }

        return Currency::convert($price, $currency);
    }

    public function getPrices()
    {
        $result = [];
        $prices = $this->currency_prices;

        foreach (self::getPeriods() as $period) {
            $result[$period] = [];
            foreach (Currency::getList() as $currency) {
                $result[$period][$currency] = (float) (empty($prices[$period][$currency]) ? Currency::convert($this->{'price_' . $period}, $currency) : number_format($prices[$period][$currency], 2));
            }
        }
        return $result;
    }

    public function getAffiliateDiscount($period)
    {
        $price = $this->getPrice($period);
        if ($this->affiliate_discount) {
            return round(($price / 100) * $this->affiliate_discount, 2);
        }
        return 0;
    }

    public function getAffiliatePrice($period)
    {
        $price = $this->getPrice($period);
        if ($this->affiliate_discount) {
            $price = ($price - $this->getAffiliateDiscount($period));
        }
        return $price;
    }

    public function getPeriodByAppId($appId)
    {
        if (in_array($appId, [$this->apple_id_yearly, $this->google_id_yearly])) {
            return 'year';
        }
        return 'month';
    }

    public static function getPeriods()
    {
        return ['monthly', 'yearly'];
    }
}
