<?php

namespace AppBundle\Entity;

/**
 * Usuario
 */
class Usuario
{
    /**
     * @var string
     */
    private $usuario;

    /**
     * @var string
     */
    private $clave;

    /**
     * @var integer
     */
    private $idusuario;

    /**
     * @var \AppBundle\Entity\Perfil
     */
    private $perfil;


    /**
     * Set usuario
     *
     * @param string $usuario
     *
     * @return Usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return string
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set clave
     *
     * @param string $clave
     *
     * @return Usuario
     */
    public function setClave($clave)
    {
        $this->clave = $clave;

        return $this;
    }

    /**
     * Get clave
     *
     * @return string
     */
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * Get idusuario
     *
     * @return integer
     */
    public function getIdusuario()
    {
        return $this->idusuario;
    }

    /**
     * Set perfil
     *
     * @param \AppBundle\Entity\Perfil $perfil
     *
     * @return Usuario
     */
    public function setPerfil(\AppBundle\Entity\Perfil $perfil = null)
    {
        $this->perfil = $perfil;

        return $this;
    }

    /**
     * Get perfil
     *
     * @return \AppBundle\Entity\Perfil
     */
    public function getPerfil()
    {
        return $this->perfil;
    }
}

