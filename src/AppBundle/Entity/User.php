<?php

namespace AppBundle\Entity;

use AppBundle\Service\HashGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type as SerializerType;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRepository")
 */
class User implements UserInterface
{

    const TOKEN_LENGTH = 32;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(name="login", type="string", length=255, nullable=false)
     */
    private $login;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true, unique=true)
     * @Serializer\Exclude
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var int
     * @ORM\Column(name="vkontakte_id", type="integer", nullable=false, unique=true)
     * @Serializer\Exclude
     */
    private $vkontakteId;

    /**
     * @var string
     * @ORM\Column(name="vk_token", type="string", length=85, nullable=false)
     * @Serializer\Exclude
     */
    private $vkToken;

    /**
     * @var string
     * @ORM\Column(name="token", type="string", length=32, nullable=false, unique=true)
     * @Serializer\Exclude
     */
    private $token;

    /**
     * @var \DateTime
     * @ORM\Column(name="registration_date", type="datetime", nullable=false)
     */
    private $registrationDate;

    /**
     * @var array
     * @ORM\Column(name="roles", type="simple_array", nullable=true)
     * @Serializer\Exclude
     */
    private $roles;

    /**
     * @var Event[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event", mappedBy="creator")
     * @SerializerType("array")
     */
    private $events;

    /**
     * @var Band[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Band", mappedBy="creator")
     * @SerializerType("array")
     */
    private $createdBands;

    public function __construct(
        string $login,
        string $name,
        int $vkontakteId,
        string $vkToken,
        string $email = null,
        string $description = null,
        string $token = null
    ) {
        $this->login = $login;
        $this->name = $name;
        $this->vkontakteId = $vkontakteId;
        $this->vkToken = $vkToken;
        $this->token = $token ?: HashGenerator::generate(self::TOKEN_LENGTH);
        $this->email = $email ?: null;
        $this->description = $description;
        $this->registrationDate = new \DateTime();
        $this->events = new ArrayCollection();
        $this->createdBands = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /** {@inheritDoc} */
    public function getRoles(): array
    {
        return array_merge(
            $this->roles,
            [
                'ROLE_USER',
            ]
        );
    }

    public function setVkToken(string $vkToken)
    {
        $this->vkToken = $vkToken;
    }

    public function updateToken()
    {
        $this->token = HashGenerator::generate(self::TOKEN_LENGTH);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /** {@inheritDoc} */
    public function getPassword() {}

    /** {@inheritDoc} */
    public function getSalt() {}

    /** {@inheritDoc} */
    public function eraseCredentials() {}

    /** {@inheritDoc} */
    public function getUsername(): string
    {
        return $this->getLogin();
    }

    public function getName(): string
    {
        return $this->name;
    }
}
