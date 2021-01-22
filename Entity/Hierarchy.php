<?php

namespace Hotfix\Bundle\GeoNameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="geo__name_hierarchy")
 * @ORM\Entity(repositoryClass="Hotfix\Bundle\GeoNameBundle\Repository\HierarchyRepository")
 */
class Hierarchy
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=GeoName::class)
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected ?GeoName $parent = null;

    /**
     * @ORM\ManyToOne(targetEntity=GeoName, inversedBy="parents")
     * @ORM\JoinColumn(name="child_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected ?GeoName $child = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?GeoName
    {
        return $this->parent;
    }

    public function setParent(GeoName $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function getChild(): ?GeoName
    {
        return $this->child;
    }

    public function setChild(GeoName $child): self
    {
        $this->child = $child;
        return $this;
    }
}

