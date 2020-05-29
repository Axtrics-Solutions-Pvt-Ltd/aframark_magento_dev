<?php
/**
 * Contributor company: Axtrics Solutions Pvt. Ltd.
 * Contributor Author : Shubham Kumar
 */
namespace Axtrics\Aframark\Api;
/**
 * Interface CatelogProductInterface
 * @api
 */
interface CatelogProductInterface
{
    /**
     * Return Generated Token.
     *
     * @return array
     */
    public function tokenGeneration();

	/**
     * Return All Product Collection.
     *
     * @return array
     */
    public function getCollection();

    /**
     * Return Count of Product Collection.
     *
     * @return count
     */
    public function countProduct();

     /**
     * Return Customer Count.
     *
     * @return count
     */
    public function countCustomer();

     /**
     * Return Customer Collection.
     *
     * @return array
     */
    public function customerCollection();
    
}
