<?php

namespace Hotfix\Bundle\GeoNameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="geo__name_hierarchy",
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(columns={"parent_id", "child_id"})
 *    }
 * )
 * @ORM\Entity(repositoryClass="Hotfix\Bundle\GeoNameBundle\Repository\HierarchyRepository")
 */
class Hierarchy
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=GeoName::class, inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected ?GeoName $parent = null;

    /**
     * @ORM\ManyToOne(targetEntity=GeoName::class, inversedBy="parents")
     * @ORM\JoinColumn(name="child_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected ?GeoName $child = null;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected ?string $type = null;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
