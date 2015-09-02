<?php
namespace Springbox\Models\Meta;

use Illuminate\Database\Eloquent\Builder;

class PostMetaModel extends BaseMetaModel
{

	/**
	 * The table to pull from.
	 * @var string
	 */
	protected $table = 'post';

	/**
	 * Define the primary key column.
	 * @var string
	 */
	protected $primaryKey = 'meta_id';

	/**
	 * Allowed columns to set.
	 * @var [type]
	 */
	protected $fillable = ['meta_key', 'meta_value'];

	/**
	 * The attributes for this model.
	 *
	 * @var array.
	 */
	protected $attributes = [
		'meta_key'   => '',
		'meta_value' => '',
	];

	/**
	 * Return the post associated with this meta.
	 *
	 * @return \Illuminate\Database\Eloquent\Relation
	 */
	protected function post()
	{
		return $this->belongsTo('Springbox\Models\Post\PostModel', 'ID');
	}

}
