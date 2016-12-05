<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cache;
use Laracasts\Presenter\PresentableTrait;
use Phphub\Presenters\SitePresenter;

class Site extends Model
{
    use PresentableTrait;
    protected $presenter = SitePresenter::class;

    protected $guarded = ['id'];

    public static function boot() {
        parent::boot();

        static::saving(function($model) {
            Cache::forget('phphub_sites');
        });
    }

    public static function allFromCache($expire = 1440)
    {
        $data = Cache::remember('phphub_sites', 60, function () {
            $raw_sites = self::orderBy('order', 'desc')->orderBy('created_at', 'desc')->get();
            $sorted = [];

            $sorted['site_search'] = $raw_sites->filter(function ($item) {
                return $item->type == 'site_search';
            });
            $sorted['site_community'] = $raw_sites->filter(function ($item) {
                return $item->type == 'site_community';
            });
            $sorted['dev_technology'] = $raw_sites->filter(function ($item) {
                return $item->type == 'dev_technology';
            });
            $sorted['site_other'] = $raw_sites->filter(function ($item) {
                return $item->type == 'site_other';
            });
            $sorted['blog'] = $raw_sites->filter(function ($item) {
                return $item->type == 'blog';
            });
            $sorted['site_shop'] = $raw_sites->filter(function ($item) {
                return $item->type == 'site_shop';
            });
            return $sorted;
        });
        return $data;
    }
}
