<?php
namespace Springbox\Models\Post;

use Springbox\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class PostModel extends BaseModel
{

	/**
	 * A published post or page.
	 */
	const POST_STATUS_PUBLISH = 'publish';

	/**
	 * Post is pending review.
	 */
	const POST_STATUS_PENDING = 'pending';

	/**
	 * A post in draft status.
	 */
	const POST_STATUS_DRAFT = 'draft';

	/**
	 * A newly created post, with no content.
	 */
	const POST_STATUS_AUTODRAFT = 'auto-draft';

	/**
	 * A post to publish in the future.
	 */
	const POST_STATUS_FUTURE = 'future';

	/**
	 * Not visible to users who are not logged in.
	 */
	const POST_STATUS_PRIVATE = 'private';

	/**
	 * A revision. see get_children.
	 */
	const POST_STATUS_INHERIT = 'inherit';

	/**
	 * Post is in trashbin.
	 * @since WP 2.9
	 */
	const POST_STATUS_TRASH = 'trash';

	/**
	 * The table to pull from.
	 * @var string
	 */
	protected $table = 'posts';

	/**
	 * Allowed columns to set.
	 * @var [type]
	 */
	protected $fillable = ['post_content', 'post_title', 'post_name', 'post_excerpt', 'comment_status', 'ping_status'];

	/**
	 * Disallowed columns to set.
	 * @var [type]
	 */
	protected $guarded = ['post_date', 'post_date_gmt', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'guid', 'comment_count'];

	/**
	 * The attributes that should be casted to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'post_content' => 'string',
	];

	/**
	 * The post type for this model.
	 *
	 * @var string
	 */
	protected $post_type = 'post';

	/**
	 * The attributes for this model.
	 *
	 * @var array.
	 */
	protected $attributes = [
		'post_status'           => 'publish',
		'post_content'          => '',
		'post_excerpt'          => '',
		'to_ping'               => '',
		'pinged'                => '',
		'post_content_filtered' => '',
		'comment_status'        => 'closed',
		'ping_status'           => 'closed',
		'post_parent'           => 0,
		'menu_order'            => 0,

		// Dates.
		'post_date'             => '0000-00-00 00:00:00',
		'post_date_gmt'         => '0000-00-00 00:00:00',
		'post_modified'         => '0000-00-00 00:00:00',
		'post_modified_gmt'     => '0000-00-00 00:00:00',

		// GUID.
		'guid'                  => ''
	];

	/**
	 * Default constructor. Overrides BaseModel::__construct().
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->attributes['post_type'] = $this->post_type;
	}

	/**
	 * Save the model to the database.
	 *
	 * @param array $options
	 *   An array of options to save.
	 *
	 * @return bool
	 *   Returns true if the post was saved, false otherwise.
	 */
	public function save(array $options = [])
	{
		// Make sure that the post_name slug attribute is set.
		if (empty($this->post_name)) {
			$this->setPostNameAttribute();
		}

		// Set dates.
		$this->handleDates();

		// Call the parent save.
		parent::save($options);
	}

	/**
	 * Insert the given attributes and set the ID on the model. Overrides
	 * Model::insertAndSetID to set the guid column from the id.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  array  $attributes
	 * @return void
	 */
	protected function insertAndSetId(Builder $query, $attributes)
	{
		$id = $query->insertGetId($attributes, $keyName = $this->getKeyName());
		$this->setAttribute($keyName, $id);

		// We have to cheat a bit and use $wpdb to perform a new update to set
		// the post's GUID.
		global $wpdb;
		$wpdb->update($wpdb->posts, array('guid' => get_permalink($id)), array('ID' => $id));
	}

	/**
	 * Sets the post_name (slug) of the post. If one is not given, uses the
	 * sanitized version of the post_title.
	 *
	 * @param string $value
	 *   A passed slug.
	 *
	 * @return boolean
	 *   Returns true if a valid slug was set.
	 */
	public function setPostNameAttribute($value = '')
	{
		// Set the slug.
		$this->attributes['post_name'] = (!empty($value)) ? sanitize_title($value) : sanitize_title($this->post_title);

		// Make sure it's a unique slug.
		$this->attributes['post_name'] = wp_unique_post_slug($this->attributes['post_name'], $this->ID, $this->attributes['post_status'], $this->attributes['post_type'], $this->attributes['post_parent']);
	}

	/**
	 * Sets the post_status of the post. If an invalid post status is passed,
	 * throws an InvalidArgumentException.
	 *
	 * @param string $value
	 *   A post status
	 *
	 * @return boolean
	 *   Returns true if the post's status was successfully saved.
	 */
	public function setPostStatusAttribute($value = '')
	{
		// Grab the allowed statuses.
		global $wp_post_statuses;
		$allowed_statuses = array_keys($wp_post_statuses);

		// Check to see if the passed status is allowed. If so, set it.
		if (in_array($value, $allowed_statuses)) {
			$this->attributes['post_status'] = $value;
		}

		// If we're at this point in the function, return an
		// InvalidArgumentException.
		throw new \InvalidArgumentException('post_status must be valid. Input was: ' . $status);
	}

	/**
	 * Sets the post_content for the post. If one is not given, defaults to
	 * an empty string.
	 *
	 * @param string $content
	 *   The submitted post content.
	 *
	 * @return boolean
	 *   Returns true if the post_content has been set.
	 */
	public function setPostContentAttribute($value = '')
	{
		$this->attributes['post_content'] = $value;
	}

	/**
	 * Every time there's a save, handle the fact that Wordpress does dates a
	 * bit differently than any other system.
	 *
	 * @return void
	 */
	protected function handleDates()
	{
		// If there's no post date set, we need to find a default post date.
		if ($this->attributes['post_date'] === '0000-00-00 00:00:00')
		{
			// If the post status is not a public post, go ahead and set the
			// default date from the current date.
			if (!in_array($this->attributes['post_status'], array('draft', 'pending', 'auto-draft')))
			{
				$this->attributes['post_date']     = current_time('mysql');
				$this->attributes['post_date_gmt'] = get_gmt_from_date($this->attributes['post_date']);
			}
		}

		// However, if we're performing an update, or the post date is still
		// empty, we need to adjust the modified time instead.
		if ($this->exists || $this->attributes['post_date'] === '0000-00-00 00:00:00')
		{
			$this->attributes['post_modified']     = current_time('mysql');
			$this->attributes['post_modified_gmt'] = current_time('mysql', 1);
		}
		else
		{
			// If it's a new post, go ahead and set the modified time to
			// whatever we set the post times to.
			$this->attributes['post_modified']     = $this->attributes['post_date'];
			$this->attributes['post_modified_gmt'] = $this->attributes['post_date_gmt'];
		}
	}

	/**
	 * Get all of the models from the database. Overrides the base behavior to
	 * add the post_type slug as a condition.
	 *
	 * @param array $columns
	 *   An array of columns to access.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function all($columns = ['*'])
	{
		// If there's a defined post type, attach that.
		$instance = new static;
		if (isset($instance->post_type) && $instance->post_type)
		{
			// Return a constrained collection.
			$columns = is_array($columns) ? $columns : func_get_args();
			return $instance->newQuery()->where('post_type', $instance->post_type)->get($columns);
		}

		// Return the parent's behavior, if nothing is set.
		return parent::all($columns);
	}

	/**
	 * Returns all published versions of the model.
	 *
	 * @param array $columns
	 *   An array of columns to access.
	 *
	 * @return Illuminate\Database\Eloquent\Builder
	 */
	protected function published($columns = ['*'])
	{
		$columns = is_array($columns) ? $columns : func_get_args();

		// If there's a defined post type, attach that.
		$instance = new static;
		$query = $instance->newQuery();
		if (isset($instance->post_type) && $instance->post_type)
		{
			// Return a constrained collection.
			$query->where('post_type', $instance->post_type);
		}

		// Attach the columns.
		$query->where('post_status', static::POST_STATUS_PUBLISH);

		// Return the query.
		return $query;
	}

	/**
	 * Overloads the default __get() magic method. Used to return a possible
	 * meta key value.
	 *
	 * @param  string $name
	 *   A meta_key name.
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		// Check to see if the meta key exists.
		if ($meta = $this->meta()->where('meta_key', $name)->first())
		{
			// Use WP's default maybe_unserialize function to handle possible
			// serialized data.
			return maybe_unserialize($meta->meta_value);
		}

		// No, the key doesn't exist, so return the parent's action.
		return parent::__get($name);
	}

	/**
	 * Handles fetching of the meta.
	 *
	 * @return \Illuminate\Database\Eloquent\Relation
	 */
	public function meta()
	{
		return $this->hasMany('Springbox\Models\Meta\PostMetaModel', 'post_id');
	}

	/**
	 * Returns the terms associated with this post.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\Relation
	 */
	public function terms()
	{
		global $wpdb;
		return $this->belongsToMany('Springbox\Models\Taxonomy\TaxonomyTermModel', $wpdb->prefix . 'term_relationships', 'object_id', 'term_taxonomy_id');
	}


}
