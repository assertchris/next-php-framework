<?php

namespace Next\Validation;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;
    
    public static function connect(\Next\App $app)
    {
        static::$connection = new \Rakit\Validation\Validator();

        $app['validation'] = static::getInstance();
    }

    public function run(Request $request, array $rules = [])
    {
        $validation = $this->validator->make($request->only(array_keys($rules)), $rules);
        $validation->validate();

        return $validation;
    }
}
