<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Infrasctucture\FormattedDateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type as SerializerType;

/**
 * User
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRepository")
 */
class User
{

    use FormattedDateTrait;

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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     * @ORM\Column(name="registration_date", type="datetime", nullable=false)
     * @SerializedName("registration_date")
     * @Accessor(getter="getDate")
     * @SerializerType("string")
     */
    private $registrationDate;

    /**
     * @var Role[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     * @ORM\JoinTable(
     *     name="users_roles",
     *     joinColumns={@ORM\JoinColumn(name="user_login", referencedColumnName="login")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_name", referencedColumnName="name")}
     *     )
     * @Accessor(getter="getRolesNames")
     * @SerializerType("array")
     */
    private $roles;

    /**
     * @param string $login
     * @param string $name
     * @param string|null $description
     */
    public function __construct(string $login, string $name, string $description = null)
    {
        $this->login = $login;
        $this->name = $name;
        $this->description = $description;
        $this->registrationDate = new \DateTime();
        $this->roles = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @return PersistentCollection|Role[]
     */
    public function getRoles(): PersistentCollection
    {
        return $this->roles;
    }

    /**
     * @return string[]
     */
    public function getRolesNames(): array
    {
        return array_map(function (Role $role) {
            return $role->getName();
        }, $this->getRoles()->toArray());
    }

    /**
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        $this->roles->add($role);
    }
}
