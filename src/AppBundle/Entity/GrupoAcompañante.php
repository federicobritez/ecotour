<?php

namespace AppBundle\Entity;

/**
 * GrupoAcompañante
 */
class GrupoAcompañante
{
    /**
     * @var integer
     */
    private $idclienteResponsable;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set idclienteResponsable
     *
     * @param integer $idclienteResponsable
     *
     * @return GrupoAcompañante
     */
    public function setIdclienteResponsable($idclienteResponsable)
    {
        $this->idclienteResponsable = $idclienteResponsable;

        return $this;
    }

    /**
     * Get idclienteResponsable
     *
     * @return integer
     */
    public function getIdclienteResponsable()
    {
        return $this->idclienteResponsable;
    }

    /**
     * Set alias
     *
     * @param string $alias
     *
     * @return GrupoAcompañante
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}

