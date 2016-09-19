<?php

namespace AppBundle\Entity;

/**
 * Acompañante
 */
class Acompañante
{
    /**
     * @var integer
     */
    private $clienteId;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \AppBundle\Entity\GrupoAcompañante
     */
    private $grupoAcompañante;


    /**
     * Set clienteId
     *
     * @param integer $clienteId
     *
     * @return Acompañante
     */
    public function setClienteId($clienteId)
    {
        $this->clienteId = $clienteId;

        return $this;
    }

    /**
     * Get clienteId
     *
     * @return integer
     */
    public function getClienteId()
    {
        return $this->clienteId;
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

    /**
     * Set grupoAcompañante
     *
     * @param \AppBundle\Entity\GrupoAcompañante $grupoAcompañante
     *
     * @return Acompañante
     */
    public function setGrupoAcompañante(\AppBundle\Entity\GrupoAcompañante $grupoAcompañante = null)
    {
        $this->grupoAcompañante = $grupoAcompañante;

        return $this;
    }

    /**
     * Get grupoAcompañante
     *
     * @return \AppBundle\Entity\GrupoAcompañante
     */
    public function getGrupoAcompañante()
    {
        return $this->grupoAcompañante;
    }
}

