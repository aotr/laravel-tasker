<?php

namespace Aotr\Tasker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, $default = null)
 */
class Configuration extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Aotr\Tasker\Facades\Configuration::class;
    }
}
