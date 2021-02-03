<?php

namespace Hotfix\Bundle\GeoNameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="geo__alternatename")
 * @ORM\Entity(repositoryClass="Hotfix\Bundle\GeoNameBundle\Repository\AlternateNameRepository")
 */
class AlternateName
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=GeoName::class)
     * @ORM\JoinColumn(name="geoname_id", referencedColumnName="id", nullable=true)
     */
    protected ?GeoName $geoName = null;

    /**
     * iso 639 language code 2- or 3-characters; 4-characters 'post' for postal codes and 'iata','icao' and faac for airport codes, fr_1793 for French Revolution names,  abbr for abbreviation, link to a website (mostly to wikipedia), wkdt for the wikidataid, varchar(7).
     *
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    protected ?string $isoLanguage = null;

    /**
     * alternate name or name variant, varchar(400).
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected ?string $name = null;

    /**
     * '1', if this alternate name is an official/preferred name.
     *
     * @ORM\Column(type="boolean")
     */
    protected ?string $isPreferredName = null;

    /**
     * '1', if this is a short name like 'California' for 'State of California'.
     *
     * @ORM\Column(type="boolean")
     */
    protected ?string $isShortName = null;

    /**
     * '1', if this alternate name is a colloquial or slang term. Example: 'Big Apple' for 'New York'.
     *
     * @ORM\Column(type="boolean")
     */
    protected ?string $isColloquial = null;

    /**
     * '1', if this alternate name is historic and was used in the past. Example 'Bombay' for 'Mumbai'.
     *
     * @ORM\Column(type="boolean")
     */
    protected ?string $isHistoric = null;

    /**
     * from period when the name was used.
     *
     * @ORM\Column(type="string", length=50)
     */
    protected ?string $yearFrom = null;

    /**
     * to period when the name was used.
     *
     * @ORM\Column(type="string", length=50)
     */
    protected ?string $yearTo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getGeoName(): ?GeoName
    {
        return $this->geoName;
    }

    public function setGeoName(?GeoName $geoName): self
    {
        $this->geoName = $geoName;

        return $this;
    }

    public function getIsoLanguage(): ?string
    {
        return $this->isoLanguage;
    }

    public function setIsoLanguage(?string $isoLanguage): self
    {
        $this->isoLanguage = $isoLanguage;

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

    public function getIsPreferredName(): ?bool
    {
        return $this->isPreferredName;
    }

    public function setIsPreferredName(?bool $isPreferredName): self
    {
        $this->isPreferredName = $isPreferredName;

        return $this;
    }

    public function getIsShortName(): ?bool
    {
        return $this->isShortName;
    }

    public function setIsShortName(?bool $isShortName): self
    {
        $this->isShortName = $isShortName;

        return $this;
    }

    public function getIsColloquial(): ?bool
    {
        return $this->isColloquial;
    }

    public function setIsColloquial(?bool $isColloquial): self
    {
        $this->isColloquial = $isColloquial;

        return $this;
    }

    public function getIsHistoric(): ?bool
    {
        return $this->isHistoric;
    }

    public function setIsHistoric(?bool $isHistoric): self
    {
        $this->isHistoric = $isHistoric;

        return $this;
    }

    public function getYearFrom(): ?string
    {
        return $this->yearFrom;
    }

    public function setYearFrom(?string $yearFrom): self
    {
        $this->yearFrom = $yearFrom;

        return $this;
    }

    public function getYearTo(): ?string
    {
        return $this->yearTo;
    }

    public function setYearTo(?string $yearTo): self
    {
        $this->yearTo = $yearTo;

        return $this;
    }
}
