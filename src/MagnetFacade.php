<?php

namespace Chameleon\YigimMagnet;

use Illuminate\Support\Facades\Facade;

class MagnetFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'yigim-magnet';
    }
}

