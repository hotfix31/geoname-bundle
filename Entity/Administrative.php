<?php

namespace Hotfix\Bundle\GeoNameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="geo__administrative")
 * @ORM\Entity(repositoryClass="Hotfix\Bundle\GeoNameBundle\Repository\AdministrativeRepository")
 */
class Administrative
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     */
    protected ?string $code = null;

    /**
     * name of geographical point (utf8) varchar(200).
     *
     * @ORM\Column(type="string", length=200)
     */
    protected ?string $name = null;

    /**
     * name of geographical point in plain ascii characters, varchar(200).
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected ?string $asciiName = null;

    /**
     * @ORM\ManyToOne(targetEntity=GeoName::class)
     * @ORM\JoinColumn(name="geoname_id", referencedColumnName="id", nullable=true)
     */
    protected ?GeoName $geoName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAsciiName(): ?string
    {
        return $this->asciiName;
    }

    public function setAsciiName(string $asciiName): self
    {
        $this->asciiName = $asciiName;

        return $this;
    }

    public function getGeoName(): ?GeoName
    {
        return $this->geoName;
    }

    public function setGeoName(GeoName $geoName): self
    {
        $this->geoName = $geoName;

        return $this;
    }
}
