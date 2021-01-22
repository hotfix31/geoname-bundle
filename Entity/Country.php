<?php

namespace Hotfix\Bundle\GeoNameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="geo__country" ,indexes={
 *     @ORM\Index(name="geoname_country_search_idx", columns={"name", "iso"})
 * })
 * @ORM\Entity(repositoryClass="Hotfix\Bundle\GeoNameBundle\Repository\CountryRepository")
 */
class Country
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="iso", type="string", length=2, nullable=false)
     */
    protected ?string $iso = null;

    /**
     * @ORM\Column(name="iso3", type="string", length=3, nullable=false)
     */
    protected ?string $iso3 = null;

    /**
     * @ORM\Column(name="iso_numeric", type="integer", length=3, nullable=false)
     */
    protected ?int $isoNumeric = null;

    /**
     * @ORM\Column(name="fips", type="string", length=2, nullable=true)
     */
    protected ?string $fips = null;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected ?string $name = null;

    /**
     * @ORM\Column(name="capital", type="string", length=255, nullable=true)
     */
    protected ?string $capital = null;

    /**
     * @ORM\Column(name="area", type="bigint", nullable=false)
     */
    protected ?int $area = null;

    /**
     * @ORM\Column(name="population", type="bigint", nullable=false)
     */
    protected ?int $population = null;

    /**
     * @ORM\Column(name="tld", type="string", length=15, nullable=true)
     */
    protected ?string $tld = null;

    /**
     * @ORM\Column(name="currency", type="string", length=3, nullable=true)
     */
    protected ?string $currency = null;

    /**
     * @ORM\Column(name="currency_name", type="string", length=50, nullable=true)
     */
    protected ?string $currencyName = null;

    /**
     * @ORM\Column(name="phone_prefix", type="integer", nullable=true)
     */
    protected ?string $phonePrefix = null;

    /**
     * @ORM\Column(name="postal_format", type="text", nullable=true)
     */
    protected ?string $postalFormat = null;

    /**
     * @ORM\Column(name="postal_regex", type="text", nullable=true)
     */
    protected ?string $postalRegex = null;

    /**
     * @ORM\Column(name="languages", type="json", nullable=true)
     */
    protected ?array $languages = null;

    /**
     * @ORM\ManyToOne(targetEntity=GeoName::class)
     * @ORM\JoinColumn(name="geoname_id", referencedColumnName="id", nullable=true)
     */
    protected ?GeoName $geoName = null;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getIso(): string
    {
        return $this->iso;
    }

    public function setIso(string $iso): self
    {
        $this->iso = $iso;
        return $this;
    }

    public function getIso3(): ?string
    {
        return $this->iso3;
    }

    public function setIso3(string $iso3): self
    {
        $this->iso3 = $iso3;
        return $this;
    }

    public function getIsoNumeric(): ?int
    {
        return $this->isoNumeric;
    }

    public function setIsoNumeric(int $isoNumeric): self
    {
        $this->isoNumeric = $isoNumeric;
        return $this;
    }

    public function getFips(): ?string
    {
        return $this->fips;
    }

    public function setFips(string $fips): self
    {
        $this->fips = $fips;
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

    public function getArea(): ?int
    {
        return $this->area;
    }

    public function setArea(string $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function setCapital(string $capital): self
    {
        $this->capital = $capital;
        return $this;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(int $population): self
    {
        $this->population = $population;
        return $this;
    }

    public function getTld(): ?string
    {
        return $this->tld;
    }

    public function setTld(string $tld): self
    {
        $this->tld = $tld;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrencyName(): ?string
    {
        return $this->currencyName;
    }

    public function setCurrencyName(string $currencyName): self
    {
        $this->currencyName = $currencyName;
        return $this;
    }

    public function getPhonePrefix(): ?int
    {
        return $this->phonePrefix;
    }

    public function setPhonePrefix(int $phonePrefix): self
    {
        $this->phonePrefix = $phonePrefix;
        return $this;
    }

    public function getPostalFormat(): ?string
    {
        return $this->postalFormat;
    }

    public function setPostalFormat(string $postalFormat): self
    {
        $this->postalFormat = $postalFormat;
        return $this;
    }

    public function getPostalRegex(): ?string
    {
        return $this->postalRegex;
    }

    public function setPostalRegex(string $postalRegex): self
    {
        $this->postalRegex = $postalRegex;
        return $this;
    }

    public function getLanguages(): ?array
    {
        return $this->languages;
    }

    public function setLanguages(array $languages): self
    {
        $this->languages = $languages;
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
