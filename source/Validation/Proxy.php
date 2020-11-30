<?php

namespace Next\Validation;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;

    public static function connect(\Next\App $app): void
    {
        static::$connection = $connection = new \Rakit\Validation\Validator();

        \Next\Http\Request::macro('validate', function (array $rules = [], array $messages = []) use ($connection) {
            /** @phpstan-ignore-next-line */
            $validation = $connection->make($this->only(array_keys($rules)), $rules, $messages);
            $validation->validate();

            return $validation;
        });
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     */
    public function run(array $data = [], array $rules = [], array $messages = []): \Rakit\Validation\Validation
    {
        $validation = static::$connection->make($data, $rules, $messages);
        $validation->validate();

        return $validation;
    }
}
