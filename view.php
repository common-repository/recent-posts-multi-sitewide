<?php

foreach($posts as $post){
	$return .=<<<HTML
		<div id="post-{$post[blog_id]}-{$post[post_id]}" class="post type-post status-publish format-standard hentry front-page">
			<h2>
				<a href="{$post[the_permalink]}" rel="bookmark" title="{$post[post_title]}">{$post[post_title]}</a>
			</h2>
			<p>
				From <a href="{$post[siteurl]}">{$post[blogname]}</a>
			</p>
			<div class="postmeta">
				Posted on 
				<span class="timestamp">
					{$post[date]} @ {$post[time]}
				</span> 
				<span class="author">by
					<a href="{$post[author_url]}" title="{$post[author_name]}" class="author">
						{$post[author_name]}
					</a>
				</span>
			</div>
			<div class="entry">
				{$post[content]}
			</div>
			<p>
				<a href="{$post[the_permalink]}">
					Read more >>>
				</a>
			</p>
		</div>
HTML;
}