<?php

if (!function_exists('env')) {

    function env($param, $default = null) {
        $env_file = __DIR__ . DS . ".env.php";
        if (!file_exists($env_file)) {
            throw new Exception('need .env.php file');
        }
        $env_config = include $env_file;

        if (isset($env_config[$param])) {
            return $env_config[$param];
        }

        return $default;
    }
}

if (!function_exists('app')) {

    function app() {
        global $app;
        return $app;
    }
}

if (!function_exists('cast')) {
    /**
     * Class casting
     *
     * @param string|object $destination
     * @param object $source_object
     * @return object
     */
    function cast($destination, $source_object)
    {
        if (is_string($destination)) {
            $destination = new $destination();
        }
        $source_reflection = new ReflectionObject($source_object);
        $destination_reflection = new ReflectionObject($destination);
        $source_properties = $source_reflection->getProperties();
        foreach ($source_properties as $source_property) {
            $source_property->setAccessible(true);
            $name = $source_property->getName();
            $value = $source_property->getValue($source_object);
            if ($destination_reflection->hasProperty($name)) {
                $prop_dest = $destination_reflection->getProperty($name);
                $prop_dest->setAccessible(true);
                $prop_dest->setValue($destination,$value);
            } else {
            }
            $destination->{$name} = $value;
        }
        return $destination;
    }
}



