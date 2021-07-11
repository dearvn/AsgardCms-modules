<?php
namespace Modules\Otp\Services;

class ServiceFactory
{
    public function getService($serviceName)
    {
        $services = config("otp.services", []);
        
        $class = isset($services[$serviceName]) && isset($services[$serviceName]["class"]) ? $services[$serviceName]["class"] : '';
        if ( $class && class_exists($class)) {
            return new $services[$serviceName]["class"]();
        } else {
            return null;
        }
    }
}
