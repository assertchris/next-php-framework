<?php

namespace Next\Validation;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;
    
    public static function connect(\Next\App $app)
    {
        static::$connection = new \Rakit\Validation\Validator();
    }

    public function run(\Next\Http\Request $request, array $rules = [])
    {
        $validation = static::$connection->make($request->only(array_keys($rules)), $rules);
        $validation->validate();

        return $validation;
    }
}
