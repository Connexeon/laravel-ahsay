<?php namespace Connexeon\Ahsay\Facades;

use Illuminate\Support\Facades\Facade;

class AhsayFacade extends Facade {

    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ahsay';
    }

}
