<?php

namespace ZF\OAuth2\Client\Authentication\Adapter;

use Zend\Authentication\Result;
use Zend\Authentication\Adapter\AdapterInterface;
use Blitzy\Entity;

class OAuth2Adapter implements AdapterInterface
{
    use \DoctrineModule\Persistence\ProvidesObjectManager;

    private $data;

    public function setData($data)
    {
        $this->data = $data['results'][0];
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function authenticate()
    {
        if (!$this->getData()) {
            throw new \Exception('No data has been set to authenticate');
        }

        $data = $this->getData();
        $user = $this->getObjectManager()->getRepository('Blitzy\Entity\User')->find($data['user_id']);

        if (!$user) {
            $user = new Entity\User;
            $user->setId($data['user_id']);
            $this->getObjectManager()->persist($user);
        }

        if (!$user->getCreatedAt()) {
            $user->setCreatedAt(new \DateTime());
        }

        $userRole = $this->getObjectManager()->getRepository('Blitzy\Entity\Role')->findOneBy([
            'roleId' => 'user'
        ]);

        if (!sizeof($user->getRole()) or !$user->getRole()->contains($userRole)) {
            $user->addRole($userRole);
            $userRole->addUser($user);
        }

        $user->setLoginName($data['login_name']);
        $user->setPrimaryEmail($data['primary_email']);
        $createdAt = \DateTime::createFromFormat('U', $data['creation_tsz']);
        $createdAt->setTimezone(new \DateTimeZone('America/New_York'));
        $user->setCreationTsz($createdAt);
        $user->setReferredBy($data['referred_by_user_id']);

        $this->getObjectManager()->flush();

        return new Result(Result::SUCCESS, $user, []);
    }
}