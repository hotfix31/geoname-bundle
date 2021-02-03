<?php

namespace Hotfix\Bundle\GeoNameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="geo__feature",
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(columns={"class", "code"})
 *    }
 * )
 * @ORM\Entity(repositoryClass="Hotfix\Bundle\GeoNameBundle\Repository\FeatureRepository")
 *
 * @see http://www.geonames.org/export/codes.html
 */
class Feature
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(type="string", length=1)
     */
    protected ?string $class = null;

    /**
     * @ORM\Column(type="string", length=5)
     */
    protected ?string $code = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected ?string $name = null;

    /**
     * @ORM\Column(type="text")
     */
    protected ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
