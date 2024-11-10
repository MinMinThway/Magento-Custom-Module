<?php

namespace MMT\Product\Api\Data;

interface CustomStoreDataInterface
{
    const ID = 'id';
    const CODE = 'code';
    const NAME = 'name';


    /**
     * get store id
     * @return int
     */
    public function getId();

    /**
     * set store id
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * get store code
     * @return string
     */
    public function getCode();

    /**
     * set store code
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Retrieve store name
     *
     * @return string
     */
    public function getName();

    /**
     * Set store name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);
}
