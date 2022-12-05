<?php
namespace Puleeno\WpMongo\Metadata;

class Schedules
{
    public function __construct()
    {
        add_filter('cron_schedules', [$this, 'registerFilters']);
    }

    public function registerFilters($schedules)
    {
        $schedules['five_minutes'] = [
            'interval' => 60 * 5,
            'display'  => __('Five Minutes'),
        ];

        return $schedules;
    }
}
