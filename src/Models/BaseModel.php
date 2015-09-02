<?php
namespace Springbox\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class BaseModel extends Eloquent
{

	/**
	 * Define the local table.
	 * @var string
	 */
	protected $table = '';

	/**
	 * Define the primary key column.
	 * @var string
	 */
	protected $primaryKey = 'ID';

	/**
	 * Use WP's timestamps by default.
	 * @var boolean
	 */
	public $timestamps = false;

	/**
	 * Default constructor. Used to attach a prefix to the table.
	 */
	public function __construct()
	{
		global $wpdb;

		// Attach the prefix to the table.
		$this->table = $wpdb->prefix . $this->table;
	}

}
