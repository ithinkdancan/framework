<?php
namespace Springbox\Models\Taxonomy;

use Springbox\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class TaxonomyTermModel extends BaseModel
{
	/**
	 * The table to pull from.
	 * @var string
	 */
	protected $table = 'term_taxonomy';

	/**
	 * Allowed columns to set.
	 * @var [type]
	 */
	protected $fillable = ['description', 'parent', 'count', 'taxonomy'];

	/**
	 * The primary key.
	 * @var string
	 */
	protected $primaryKey = 'term_id';

	/**
	 * The taxonomy_slug for this model.
	 *
	 * @var string
	 */
	protected $taxonomy_slug = '';

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
	public function term()
	{
		return $this->hasOne('Springbox\Models\Taxonomy\TermModel', 'term_id', 'term_id');
	}

	/**
	 * Defines the parent relationship between hierarchical terms.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\Relation
	 */
	public function parent()
	{
		return $this->belongsTo('Springbox\Models\Taxonomy\TaxonomyTermModel', 'parent', 'term_id');
	}

	/**
	 * Defines the children relationship between hierarchical terms.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\Relation
	 */
	public function children()
	{
		return $this->hasMany('Springbox\Models\Taxonomy\TaxonomyTermModel', 'parent', 'term_id');
	}

	/**
	 * Get all of the models from the database. Overrides the base behavior to
	 * add the category slug as a condition.
	 *
	 * @param array $columns
	 *   An array of columns to access.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function all($columns = ['*'])
	{
		// If there's a defined taxonomy slug, attach that.
		$instance = new static;
		if (isset($instance->taxonomy_slug) && $instance->taxonomy_slug)
		{
			// Return a constrained collection.
			$columns = is_array($columns) ? $columns : func_get_args();
			return $instance->newQuery()->where('taxonomy', $instance->taxonomy_slug)->get($columns);
		}

		// Return the parent's behavior, if nothing is set.
		return parent::all($columns);
	}

}
