<?php


namespace App\Message;

use App\Entity\User;

class RegisterMessage
{
    private $user;
    private $password;

    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
    
}
