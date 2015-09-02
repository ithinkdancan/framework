<?php
namespace Springbox\Models\Taxonomy;

use Springbox\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class TermModel extends BaseModel
{
	/**
	 * The table to pull from.
	 * @var string
	 */
	protected $table = 'terms';

	/**
	 * Allowed columns to set.
	 * @var [type]
	 */
	protected $fillable = ['name', 'slug', 'term_group'];

	/**
	 * The primary key.
	 * @var string
	 */
	protected $primaryKey = 'term_id';

	/**
	 * Default constructor. Overrides BaseModel::__construct().
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->attributes['taxonomy'] = $this->taxonomy_slug;
	}

	/**
	 * Defines the relationship between the Term and the TaxonomyTerm.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\Relation
	 */
	public function taxonomyTerm()
	{
		return $this->belongsTo('Springbox\Models\Taxonomy\TaxonomyTermModel', 'term_id', 'term_id');
	}

	/**
	 * Returns the posts associated with this term.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\Relation
	 */
	public function posts()
	{
		global $wpdb;
		return $this->belongsToMany('Springbox\Models\Post\PostModel', $wpdb->prefix . 'term_relationships', 'term_taxonomy_id', 'object_id');
	}
}
