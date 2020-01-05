<?php

use Timber\LocationManager;

/**
 * @group called-post-constructor
 */
class TestTimberMainClass extends Timber_UnitTestCase {

	function testSample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function testGetPostNumeric(){
		$post_id = $this->factory->post->create();
		$post = Timber::get_post($post_id);
		$this->assertEquals('Timber\Post', get_class($post));
	}

	function testGetPostByPostObject() {
		$pid     = $this->factory->post->create();
		$wp_post = get_post( $pid );
		$post    = Timber\Timber::get_post( $wp_post );
		$this->assertEquals( $pid, $post->ID );
		$post = Timber\Timber::get_post( $wp_post );
		$this->assertEquals( $pid, $post->ID );
	}

	function testGetPostByQueryArray() {
		$pid = $this->factory->post->create();
		$posts = Timber\Timber::get_posts( [
			'post_type' => 'post'
		] );
		$this->assertEquals($pid, $posts[0]->ID);
		$post = Timber\Timber::get_post( [ 'post_type' => 'post' ]);
		$this->assertEquals($pid, $post->ID);
	}

	function testGetPostWithCustomPostType() {
		register_post_type('event', array('public' => true));
		$pid = $this->factory->post->create(array('post_type' => 'event'));
		$post = Timber\Timber::get_post($pid);
		$this->assertEquals($pid, $post->ID);
		$this->assertEquals('event', $post->post_type);
	}

	function testGetPostWithCustomPostTypeNotPublic() {
		register_post_type('event', array('public' => false));
		$pid = $this->factory->post->create(array('post_type' => 'event'));
		$post = Timber\Timber::get_post($pid);
		$this->assertEquals($pid, $post->ID);
	}

	function testGetPostsQueryString(){
		$this->factory->post->create();
		$this->factory->post->create();
		$posts = Timber\Timber::get_posts( [
			'post_type' => 'post',
		] );
		$this->assertGreaterThan(1, count($posts));
	}

	function testGetPostsQueryArray(){
		$this->factory->post->create();
		$posts = Timber\Timber::get_posts( [ 'post_type' => 'post' ] );
		$this->assertEquals('Timber\Post', get_class($posts[0]));
	}

	function testGetPostsQueryStringClassName(){
		$this->factory->post->create();
		$this->factory->post->create();
		$posts = Timber\Timber::get_posts( [
			'post_type' => 'post',
		] );
		$post = $posts[0];
		$this->assertEquals('Timber\Post', get_class($post));
	}

	function testGetPostsFromArrayOfIds(){
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts  = Timber\Timber::get_posts( $pids );
		$this->assertEquals('Timber\Post', get_class($posts[0]));
	}

	function testGetPostsArrayCount(){
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts  = Timber\Timber::get_posts( $pids );
		$this->assertEquals(3, count($posts));
	}

	function testGetPostsCollection() {
		$pids   = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts  = new Timber\PostCollection($pids);
		$this->assertEquals(3, count($posts));
		$this->assertEquals('Timber\PostCollection', get_class($posts));
	}

	function testUserInContextAnon() {
		$context = Timber::context();
		$this->assertArrayHasKey( 'user', $context );
		$this->assertFalse($context['user']);
	}

	function testUserInContextLoggedIn() {
		$uid = $this->factory->user->create(array(
			'user_login' => 'timber',
			'user_pass' => 'timber',
		));
		$user = wp_set_current_user($uid);

		$context = Timber::context();
		$this->assertArrayHasKey( 'user', $context );
		$this->assertInstanceOf( 'Timber\User', $context['user'] );
	}

	function testQueryPostsInContext(){
		$pids = $this->factory->post->create_many(20);
		$this->go_to('/');
        $context = Timber::context();
        $this->assertArrayHasKey( 'posts', $context );
        $this->assertInstanceOf( 'Timber\PostCollection', $context['posts'] );
	}

	/* Terms */
	function testGetTerm(){
		// @todo #2087
		$this->markTestSkipped();
	}

	function testGetTermWithTaxonomyParam(){
		// @todo #2087
		$this->markTestSkipped();
	}

	function testGetTermWithObject(){
		// @todo #2087
		$this->markTestSkipped();
	}

	function testGetTermWithSlug(){
		// @todo #2087
		$this->markTestSkipped();
		$term_id = $this->factory->term->create(array('name' => 'New England Patriots'));
		$term = Timber::get_term('new-england-patriots');
		$this->assertEquals($term->ID, $term_id);
	}

	function testGetTerms(){
		$posts = $this->factory->post->create_many(15, array( 'post_type' => 'post' ) );
		$tags = array();
		foreach($posts as $post){
			$tag = rand_str();
			wp_set_object_terms($post, $tag, 'post_tag');
			$tags[] = $tag;
		}
		sort($tags);
		$terms = Timber::get_terms('tag');
		$this->assertEquals('Timber\Term', get_class($terms[0]));
		$results = array();
		foreach($terms as $term){
			$results[] = $term->name;
		}
		sort($results);
		$this->assertEquals($results, $tags);

	}

    /* Previews */
    function testGetPostPreview(){
        $editor_user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
        wp_set_current_user( $editor_user_id );

        $post_id = $this->factory->post->create( array( 'post_author' => $editor_user_id, 'post_content' => "OLD CONTENT HERE" ) );
        _wp_put_post_revision( array( 'ID' => $post_id, 'post_content' => 'New Stuff Goes here'), true );

        $_GET['preview']    = true;
        $_GET['preview_id'] = $post_id;

        $the_post = Timber::get_post( $post_id );
        $this->assertEquals( 'New Stuff Goes here', $the_post->post_content );
    }

    function testTimberRenderString() {
    	$pid = $this->factory->post->create(array('post_title' => 'Zoogats'));
        $post = Timber::get_post($pid);
        ob_start();
        Timber::render_string('<h2>{{post.title}}</h2>', array('post' => $post));
       	$data = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('<h2>Zoogats</h2>', trim($data));
    }

    function testTimberRender() {
    	$pid = $this->factory->post->create(array('post_title' => 'Foobar'));
        $post = Timber::get_post($pid);
        ob_start();
        Timber::render('assets/single-post.twig', array('post' => $post));
       	$data = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('<h1>Foobar</h1>', trim($data));
    }

    function testTimberGetCallingScriptFile() {
    	$calling_file = LocationManager::get_calling_script_file();
    	$file = getcwd().'/tests/test-timber.php';
    	$this->assertEquals($calling_file, $file);
    }

    function testCompileNull() {
    	$str = Timber::compile('assets/single-course.twig', null);
    	$this->assertEquals('I am single course', $str);
    }

    /**
	 * @ticket 1660
     * @todo Maybe this can be deleted?
	 */
	function testDoubleInstantiationOfSubclass() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'person' ) );
		$post = Timber\Timber::get_post($post_id, 'Person');
		$this->assertEquals('Person', get_class($post));
	}

	/**
	 * @ticket 1660
     * @todo Maybe this can be deleted?
	 */
	function testDoubleInstantiationOfTimberPostClass() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$post = Timber\Timber::get_post($post_id);
		$this->assertEquals('Timber\Post', get_class($post));
	}

	/**
	 * @expectedIncorrectUsage Timber::get_post()
	 */
	function testDeprecatedGetPostFromSlug(){
		$post_id = $this->factory->post->create( [ 'post_name' => 'mycoolpost' ] );
		$post    = Timber\Timber::get_post( 'mycoolpost' );
		$this->assertEquals($post_id, $post->ID);

		$post = Timber\Timber::get_post( 'mycoolpost' );
		$this->assertEquals($post_id, $post->ID);
	}

	/**
	 * @expectedIncorrectUsage Timber::get_post()
	 */
	function testDeprecatedPostClassParameterForGetPost() {
		require_once __DIR__ . '/php/timber-post-subclass.php';

		$post_id = $this->factory->post->create();
		$post    = Timber\Timber::get_post( $post_id, TimberPostSubclass::class );

		$this->assertEquals( 'Timber\Post', get_class( $post ) );
	}

	/**
	 * @expectedIncorrectUsage Timber::get_posts()
	 */
	function testDeprecatedPostClassParameterForGetPosts() {
		require_once __DIR__ . '/php/timber-post-subclass.php';

		$this->factory->post->create_many( 2 );

		$posts = Timber\Timber::get_posts(
			[ 'post_type' => 'post' ],
			TimberPostSubclass::class
		);

		$this->assertEquals( 'Timber\Post', get_class( $posts[0] ) );
	}

	/**
	 * @expectedIncorrectUsage Timber::get_posts()
	 */
	function testDeprecatedQueryStringsForGetPosts() {
		$this->factory->post->create_many( 2 );

		$posts = Timber\Timber::get_posts( 'post_type=post' );
		$this->assertGreaterThan( 1, count( $posts ) );
	}

	/**
	 * @expectedIncorrectUsage Timber::get_posts()
	 */
	function testDeprecatedReturnCollectionParameterInGetPosts() {
		$this->factory->post->create_many( 2 );

		$posts = Timber\Timber::get_posts(
			[ 'post_type' => 'post' ],
			'Timber\Post',
			true
		);

		$this->assertEquals( 'Timber\Post', get_class( $posts[0] ) );
	}

	/**
	 * @expectedDeprecated Timber::query_post()
	 */
	function testDeprecatedQueryPost() {
		$post_id = $this->factory->post->create( [ 'post_type' => 'post' ] );
		$post    = Timber\Timber::query_post( $post_id );

		$this->assertEquals( $post->ID, $post_id );
	}

	/**
	 * @expectedDeprecated Timber::query_posts()
	 */
	function testDeprecatedQueryPosts() {
		$post_ids = $this->factory->post->create_many( 3, [ 'post_type' => 'post' ] );
		$posts    = Timber\Timber::query_posts( [ 'post_type' => 'post' ] );

		$this->assertEquals( $posts[0]->ID, $post_ids[0] );
	}
}
