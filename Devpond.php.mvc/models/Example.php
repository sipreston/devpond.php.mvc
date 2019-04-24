<?php

namespace Models;

use Vision\VisionModel\Model;

class Example extends Model
{
    /**
     * The parent Model class contains the get/save methods which automatically populate
     * or create/update the object properties from database values.
     */

    #region properties
    //id of object is set in parent Model class

    /**
     * @model - tells the code this property is to be used in the ORM mapping
     * @property - this property can be seen by magic gets/sets
     * @var string
     */
    private $name;
    /**
     * @model
     * @property-read - this property can be seen by gets
     * @var \DateTime
     */
    private $dateCreated;
	/**
     * @model
     * @property-write - this property can be seen by magic sets
     * @var string
     */
	private $description;

	#endregion

	#region collections
	/**
     *	Collections are related table data. In this example there would be a table ExampleAssociations,
     *	linked by the ID. The data is retrieved from there.
     */

	/**
     * @model
     * @property-write this property can be seen by magic sets
     * @var Associations[]
     */
	private $associations;

	#endregion

	#region methods
	/**
     * Set any methods related to the class here
     *
     */
	public function getDescription()
    {
        return $this->description;
    }

	#endregion
}