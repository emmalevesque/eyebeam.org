<?php

if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_page',
		'title' => 'Page',
		'fields' => array (
			array (
				'key' => 'field_568ac2197cee7002',
				'label' => 'Summary',
				'name' => 'summary',
				'type' => 'wysiwyg',
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 'yes',
			),
			array (
				'key' => 'field_56f68100565422',
				'label' => 'Image',
				'name' => 'image',
				'type' => 'image',
				'save_format' => 'id',
				'preview_size' => 'thumbnail',
				'library' => 'all',
			),
			array (
				'key' => 'field_5aa1b7e8b1f222',
				'label' => 'Show date',
				'name' => 'show_date',
				'type' => 'radio',
				'choices' => array (
					'show' => 'Show publish date',
					'hide' => 'Hide date',
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => 'hide',
				'layout' => 'horizontal',
			),
			array (
				'key' => 'field_5aa1b70edd6c62',
				'label' => 'Meta',
				'name' => 'meta',
				'type' => 'wysiwyg',
				'instructions' => 'override meta box content',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_5aa1b7e8b1f222',
							'operator' => '==',
							'value' => 'hide',
						),
					),
					'allorany' => 'all',
				),
				'default_value' => '',
				'toolbar' => 'basic',
				'media_upload' => 'no',
			),
			array (
				'key' => 'field_56f5cf38048412',
				'label' => 'Author',
				'name' => 'author',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_5aa1b7e8b1f592',
				'label' => 'Show Related Readings Automatically',
				'name' => 'show_related',
				'type' => 'radio',
				'choices' => array (
					'auto' => 'Show Related Readings Automatically',
					'manual' => 'Manually Select Related Readings',
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => 'auto',
				'layout' => 'horizontal',
			),
			array (
				'key' => 'field_565aee13pi2',
				'label' => 'Media',
				'name' => 'media',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_565bbe48pf2',
						'label' => 'Media type',
						'name' => 'media_type',
						'type' => 'radio',
						'column_width' => '',
						'choices' => array (
							'image' => 'Image',
							'video_url' => 'Video URL',
						),
						'other_choice' => 0,
						'save_other_choice' => 0,
						'default_value' => '',
						'layout' => 'horizontal',
					),
					array (
						'key' => 'field_576cbc67rv2',
						'label' => 'Image',
						'name' => 'image',
						'type' => 'image',
						'conditional_logic' => array (
							'status' => 1,
							'rules' => array (
								array (
									'field' => 'field_565bbe48pf2',
									'operator' => '==',
									'value' => 'image',
								),
							),
							'allorany' => 'all',
						),
						'column_width' => '',
						'save_format' => 'id',
						'preview_size' => 'thumbnail',
						'library' => 'all',
					),
					array (
						'key' => 'field_558cac42c32',
						'label' => 'Video URL',
						'name' => 'video_url',
						'type' => 'text',
						'instructions' => 'currently only YouTube URLs work',
						'conditional_logic' => array (
							'status' => 1,
							'rules' => array (
								array (
									'field' => 'field_565bbe48pf2',
									'operator' => '==',
									'value' => 'video_url',
								),
							),
							'allorany' => 'all',
						),
						'column_width' => '',
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_556aba43rg2',
						'label' => 'Caption',
						'name' => 'caption',
						'type' => 'wysiwyg',
						'column_width' => '',
						'default_value' => '',
						'toolbar' => 'basic',
						'media_upload' => 'no',
					),
				),
				'row_min' => '',
				'row_limit' => '',
				'layout' => 'row',
				'button_label' => 'Add Media',
			),
			array (
				'key' => 'field_586bce21ov672',
				'label' => 'Related Readings',
				'name' => 'related_readings',
				'type' => 'relationship',
				'return_format' => 'object',
				'post_type' => array (
					0 => 'event,project,resident,archive',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'filters' => array (
					0 => 'search',
				),
				'result_elements' => array (
					0 => 'post_type',
					1 => 'post_title',
				),
				'max' => '',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_5aa1b7e8b1f592',
							'operator' => '==',
							'value' => 'manual',
						),
					),
					'allorany' => 'all',
				),
			),
			array (
				'key' => 'field_5aa1b7e8b1f66692',
				'label' => 'Display Tags',
				'name' => 'show_tags',
				'type' => 'radio',
				'choices' => array (
					'show' => 'Show Tags',
					'hide' => 'Hide Tags',
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => 'show',
				'layout' => 'horizontal',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (

			),
		),
		'menu_order' => 0,
	));
}
