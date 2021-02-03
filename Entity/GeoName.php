<?php

namespace Hotfix\Bundle\GeoNameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="geo__name" ,indexes={
 *     @ORM\Index(name="geoname_geoname_search_idx", columns={"name", "country_code"}),
 * })
 * @ORM\Entity(repositoryClass="Hotfix\Bundle\GeoNameBundle\Repository\GeoNameRepository")
 */
class GeoName
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected ?int $id = null;

    /**
     * name of geographical point (utf8) varchar(200).
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=false)
     */
    protected ?string $name = null;

    /**
     * name of geographical point in plain ascii characters, varchar(200).
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
     * @ORM\ManyToOne(targetEntity=Feature::class)
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    protected ?Feature $feature = null;

    /**
     * ISO-3166 2-letter country code, 2 characters.
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
     * alternate country codes, comma separated, ISO-3166 2-letter country code, 200 characters.
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
     * in meters, integer.
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
     * the iana timezone id.
     *
     * @ORM\ManyToOne(targetEntity=Timezone::class)
     * @ORM\JoinColumn(name="timezone_id", referencedColumnName="id", nullable=true)
     */
    protected ?Timezone $timezone = null;

    /**
     * date of last modification in yyyy-MM-dd format.
     *
     * @ORM\Column(name="modification_date", type="date", nullable=true)
     */
    protected ?\DateTimeInterface $modificationDate = null;

    /**
     * @ORM\OneToMany(targetEntity=Hierarchy::class, mappedBy="child")
     */
    protected Collection $parents;

    /**
     * @ORM\OneToMany(targetEntity=Hierarchy::class, mappedBy="parent")
     */
    protected Collection $children;

    public function __construct()
    {
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

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

    public function getAsciiName(): ?string
    {
        return $this->asciiName;
    }

    public function setAsciiName(?string $asciiName): self
    {
        $this->asciiName = $asciiName;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getFeature(): ?Feature
    {
        return $this->feature;
    }

    public function setFeature(?Feature $feature): self
    {
        $this->feature = $feature;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCc2(): ?string
    {
        return $this->cc2;
    }

    public function setCc2(?string $cc2): self
    {
        $this->cc2 = $cc2;

        return $this;
    }

    public function getAdmin1(): ?Administrative
    {
        return $this->admin1;
    }

    public function setAdmin1(?Administrative $admin1): self
    {
        $this->admin1 = $admin1;

        return $this;
    }

    public function getAdmin2(): ?Administrative
    {
        return $this->admin2;
    }

    public function setAdmin2(?Administrative $admin2): self
    {
        $this->admin2 = $admin2;

        return $this;
    }

    public function getAdmin3(): ?Administrative
    {
        return $this->admin3;
    }

    public function setAdmin3(?Administrative $admin3): self
    {
        $this->admin3 = $admin3;

        return $this;
    }

    public function getAdmin4(): ?Administrative
    {
        return $this->admin4;
    }

    public function setAdmin4(?Administrative $admin4): self
    {
        $this->admin4 = $admin4;

        return $this;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): self
    {
        $this->population = $population;

        return $this;
    }

    public function getElevation(): ?int
    {
        return $this->elevation;
    }

    public function setElevation(?int $elevation): self
    {
        $this->elevation = $elevation;

        return $this;
    }

    public function getDem(): ?int
    {
        return $this->dem;
    }

    public function setDem(?int $dem): self
    {
        $this->dem = $dem;

        return $this;
    }

    public function getTimezone(): ?Timezone
    {
        return $this->timezone;
    }

    public function setTimezone(?Timezone $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getModificationDate(): ?\DateTimeInterface
    {
        return $this->modificationDate;
    }

    public function setModificationDate(?\DateTimeInterface $modificationDate): self
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(Hierarchy $parent): self
    {
        if (!$this->parents->contains($parent)) {
            $this->parents->add($parent);
            $parent->setChild($this);
        }

        return $this;
    }

    public function removeParent(Hierarchy $parent): self
    {
        if ($this->parents->contains($parent)) {
            $this->parents->removeElement($parent);
            $parent->setChild(null);
        }

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Hierarchy $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Hierarchy $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }

        return $this;
    }
}
