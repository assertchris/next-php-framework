<?php

namespace Next\Validation;

use Next\Http\Request;
use Rakit\Validation\Validator;

class Manager
{
    private static $instance;

    private Validator $validator;

    public static function instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function __callStatic(string $method, $params)
    {
        return static::instance()->{"_{$method}"}(...$params);
    }

    public function __call(string $method, $params)
    {
        return $this->{"_{$method}"}(...$params);
    }

    private function _validate(Request $request, array $rules = [])
    {
        if (!isset($this->validator)) {
            $this->validator = new Validator();
        }

        $validation = $this->validator->make($request->only(array_keys($rules)), $rules);
        $validation->validate();

        return $validation;
    }
}
