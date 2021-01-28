<?php

namespace Hotfix\Bundle\GeoNameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="geo__timezone")
 * @ORM\Entity(repositoryClass="Hotfix\Bundle\GeoNameBundle\Repository\TimezoneRepository")
 */
class Timezone implements \Stringable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * the iana timezone id
     *
     * @ORM\Column(type="string", length=50, unique=true)
     */
    protected ?string $timezone = null;

    /**
     * @ORM\Column(type="string", length=2)
     */
    protected ?string $countryCode = null;

    /**
     * @ORM\Column(type="float", scale=1)
     */
    protected ?float $gmtOffset = null;

    /**
     * @ORM\Column(type="float", scale=1)
     */
    protected ?float $dstOffset = null;

    /**
     * @ORM\Column(type="float", scale=1)
     */
    protected ?float $rawOffset = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getGmtOffset(): ?float
    {
        return $this->gmtOffset;
    }

    public function setGmtOffset(float $gmtOffset): self
    {
        $this->gmtOffset = $gmtOffset;
        return $this;
    }

    public function getDstOffset(): ?float
    {
        return $this->dstOffset;
    }

    public function setDstOffset(float $dstOffset): self
    {
        $this->dstOffset = $dstOffset;
        return $this;
    }

    public function getRawOffset(): ?float
    {
        return $this->rawOffset;
    }

    public function setRawOffset($rawOffset): self
    {
        $this->rawOffset = $rawOffset;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getTimezone() ?? '';
    }
}
