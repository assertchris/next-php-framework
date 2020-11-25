<?php

namespace Next\Validation;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;
    
    public static function connect(\Next\App $app)
    {
        static::$connection = $connection = new \Rakit\Validation\Validator();

        \Next\Http\Request::macro('validate', function (array $rules = [], array $messages = []) use ($connection) {
            $validation = $connection->make($this->only(array_keys($rules)), $rules, $messages);

            $validation->validate();

            return $validation;
        });
    }

    public function run(\Next\Http\Request $request, array $rules = [])
    {
        $validation = static::$connection->make($request->only(array_keys($rules)), $rules);
        $validation->validate();

        return $validation;
    }
}
