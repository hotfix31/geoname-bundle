<?php

namespace Hotfix\Bundle\GeoNameBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="geo__name" ,indexes={
 *     @ORM\Index(name="geoname_geoname_search_idx", columns={"name", "country_code"}),
 *     @ORM\Index(name="geoname_feature_code_idx", columns={"feature_code"})
 * })
 * @ORM\Entity(repositoryClass="Hotfix\Bundle\GeoNameBundle\Repository\GeoNameRepository")
 */
class GeoName
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected ?int $id = null;

    /**
     * name of geographical point (utf8) varchar(200)
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=false)
     */
    protected ?string $name = null;

    /**
     * name of geographical point in plain ascii characters, varchar(200)
     *
     * @ORM\Column(name="ascii_name", type="string", length=200, nullable=true)
     */
    protected ?string $asciiName = null;


    /**
     * @ORM\Column(name="latitude", type="float", scale=6, precision=9, nullable=true)
     */
    protected ?float $latitude = null;

    /**
     * @ORM\Column(name="longitude", type="float", scale=6, precision=9, nullable=true)
     */
    protected ?float $longitude = null;

    /**
     * @see http://www.geonames.org/export/codes.html
     * @ORM\Column(name="feature_class", type="string", length=1, nullable=true)
     */
    protected ?string $featureClass = null;

    /**
     * @see http://www.geonames.org/export/codes.html
     * @ORM\Column(name="feature_code", type="string", length=10, nullable=true)
     */
    protected ?string $featureCode = null;

    /**
     * ISO-3166 2-letter country code, 2 characters
     *
     * @ORM\Column(name="country_code", type="string", length=2, nullable=true)
     */
    protected ?string $countryCode = null;

    /**
     * @ORM\ManyToOne(targetEntity=Country::class)
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected ?Country $country = null;

    /**
     * alternate country codes, comma separated, ISO-3166 2-letter country code, 200 characters
     *
     * @ORM\Column(name="cc2", type="string", length=200, nullable=true)
     */
    protected ?string $cc2 = null;

    /**
     * @ORM\ManyToOne(targetEntity=Administrative::class)
     * @ORM\JoinColumn(name="admin1_id", referencedColumnName="id", nullable=true)
     */
    protected ?Administrative $admin1 = null;

    /**
     * @ORM\ManyToOne(targetEntity=Administrative::class)
     * @ORM\JoinColumn(name="admin2_id", referencedColumnName="id", nullable=true)
     */
    protected ?Administrative $admin2 = null;

    /**
     * @ORM\ManyToOne(targetEntity=Administrative::class)
     * @ORM\JoinColumn(name="admin3_id", referencedColumnName="id", nullable=true)
     */
    protected ?Administrative $admin3 = null;

    /**
     * @ORM\ManyToOne(targetEntity=Administrative::class)
     * @ORM\JoinColumn(name="admin4_id", referencedColumnName="id", nullable=true)
     */
    protected ?Administrative $admin4 = null;

    /**
     * @ORM\Column(name="population", type="bigint", nullable=true)
     */
    protected ?int $population = null;

    /**
     * in meters, integer
     *
     * @ORM\Column(name="elevation", type="integer", nullable=true)
     */
    protected ?int $elevation = null;

    /**
     * digital elevation model, srtm3 or gtopo30, average elevation of 3''x3'' (ca 90mx90m) or 30''x30'' (ca 900mx900m) area in meters, integer. srtm processed by cgiar/ciat.
     *
     * @ORM\Column(name="dem", type="integer", nullable=true)
     */
    protected ?int $dem = null;

    /**
     * the iana timezone id
     *
     * @ORM\ManyToOne(targetEntity=Timezone::class)
     * @ORM\JoinColumn(name="timezone_id", referencedColumnName="id", nullable=true)
     */
    protected ?Timezone $timezone = null;

    /**
     * date of last modification in yyyy-MM-dd format
     *
     * @ORM\Column(name="modification_date", type="date", nullable=true)
     */
    protected ?\DateTimeInterface $modificationDate = null;

    /**
     * @ORM\OneToMany(targetEntity=Hierarchy::class, mappedBy="child")
     */
    protected ?Collection $parents = null;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return GeoName
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return GeoName
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAsciiName(): ?string
    {
        return $this->asciiName;
    }

    /**
     * @param string $asciiName
     * @return GeoName
     */
    public function setAsciiName(?string $asciiName): self
    {
        $this->asciiName = $asciiName;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param float|null $latitude
     * @return GeoName
     */
    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param float|null $longitude
     * @return GeoName
     */
    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFeatureClass(): ?string
    {
        return $this->featureClass;
    }

    /**
     * @param string|null $featureClass
     * @return GeoName
     */
    public function setFeatureClass(?string $featureClass): self
    {
        $this->featureClass = $featureClass;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFeatureCode(): ?string
    {
        return $this->featureCode;
    }

    /**
     * @param string|null $featureCode
     * @return GeoName
     */
    public function setFeatureCode(?string $featureCode): self
    {
        $this->featureCode = $featureCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @param string|null $countryCode
     * @return GeoName
     */
    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country|null $country
     * @return GeoName
     */
    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCc2(): ?string
    {
        return $this->cc2;
    }

    /**
     * @param string|null $cc2
     * @return GeoName
     */
    public function setCc2(?string $cc2): self
    {
        $this->cc2 = $cc2;

        return $this;
    }

    /**
     * @return Administrative|null
     */
    public function getAdmin1(): ?Administrative
    {
        return $this->admin1;
    }

    /**
     * @param Administrative|null $admin1
     * @return GeoName
     */
    public function setAdmin1(?Administrative $admin1): self
    {
        $this->admin1 = $admin1;

        return $this;
    }

    /**
     * @return Administrative|null
     */
    public function getAdmin2(): ?Administrative
    {
        return $this->admin2;
    }

    /**
     * @param Administrative|null $admin2
     * @return GeoName
     */
    public function setAdmin2(?Administrative $admin2): self
    {
        $this->admin2 = $admin2;

        return $this;
    }

    /**
     * @return Administrative|null
     */
    public function getAdmin3(): ?Administrative
    {
        return $this->admin3;
    }

    /**
     * @param Administrative|null $admin3
     * @return GeoName
     */
    public function setAdmin3(?Administrative $admin3): self
    {
        $this->admin3 = $admin3;

        return $this;
    }

    /**
     * @return Administrative|null
     */
    public function getAdmin4(): ?Administrative
    {
        return $this->admin4;
    }

    /**
     * @param Administrative|null $admin4
     * @return GeoName
     */
    public function setAdmin4(?Administrative $admin4): self
    {
        $this->admin4 = $admin4;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPopulation(): ?int
    {
        return $this->population;
    }

    /**
     * @param int|null $population
     * @return GeoName
     */
    public function setPopulation(?int $population): self
    {
        $this->population = $population;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getElevation(): ?int
    {
        return $this->elevation;
    }

    /**
     * @param int|null $elevation
     * @return GeoName
     */
    public function setElevation(?int $elevation): self
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDem(): ?int
    {
        return $this->dem;
    }

    /**
     * @param int|null $dem
     * @return GeoName
     */
    public function setDem(?int $dem): self
    {
        $this->dem = $dem;

        return $this;
    }

    /**
     * @return Timezone|null
     */
    public function getTimezone(): ?Timezone
    {
        return $this->timezone;
    }

    /**
     * @param Timezone|null $timezone
     * @return GeoName
     */
    public function setTimezone(?Timezone $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getModificationDate(): ?\DateTimeInterface
    {
        return $this->modificationDate;
    }

    /**
     * @param \DateTimeInterface|null $modificationDate
     * @return GeoName
     */
    public function setModificationDate(?\DateTimeInterface $modificationDate): self
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * @return Collection|null
     */
    public function getParents(): ?Collection
    {
        return $this->parents;
    }

    /**
     * @param Collection|null $parents
     * @return GeoName
     */
    public function setParents(?Collection $parents): self
    {
        $this->parents = $parents;

        return $this;
    }
}
