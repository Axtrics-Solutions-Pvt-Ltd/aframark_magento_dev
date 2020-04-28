<?php
namespace Axtrics\Aframark\Api;
 
/**
 * @api
 * @since 101.0.0
 */
interface CatelogProductInterface
{
    /**
     * Updates the specified products in item array.
     *
     * @api
     * @param mixed $data
     * @return boolean
     */
    //authorization and token generation
    public function tokenGeneration();
	/**
     * Updates the specified products list item array.
     *
     * @api
     * @param mixed $data
     * @return boolean
     */
    //counts the number of products
    public function getCollection();
    /**
     * Updates the specified products count item array.
     *
     * @api
     * @param mixed $data
     * @return boolean
     */
    //counts the number of products
    public function countProduct();
    /**
     * Get the specified customer count item array.
     *
     * @api
     * @param mixed $data
     * @return boolean
     */
    //counts the number of customers
    public function countCustomer();
    /**
     * Get the customer collection item array.
     *
     * @api
     * @param mixed $data
     * @return boolean
     */
    //get the customers collection
    public function customerCollection();
    
}
