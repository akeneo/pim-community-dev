UI DataBlock Config Overview
============================

This functionality add ability to config DataBlocks for UI component inside FromType instead of template


Configure block in template:
----------------------------

update.html.twig

```
Twig
{% set dataBlocks = [{
            'title': 'First Block',
            'class': '',
            'subblocks': [
                {
                    'title': '',
                    'data': [
                        form_row(form.name),
                        form_row(form.age)
                    ]
                },
                {
                    'title': 'Email SubBlock',
                    'data': [
                        form_row(form.email),
                    ]
                }
            ]
        }]

%}
```


Configure block in FormType
---------------------------

```
Php
class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('block' => 'first' ));
        $builder->add('age', 'integer', array('block' => 'first', 'subblock' => 'first'));
        $builder->add('email', 'email', array('block' => 'second'));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'block_config' => array(
                    'first'  => array(
                        'priority'  => 2,
                        'title'     => 'First Block',
                        'subblocks' => array(
                            'first'  => array(),
                            'second' => array(
                                'title' => 'Email SubBlock'
                            ),
                        ),
                    ),
                ),
            )
        );
    }
}


Twig
{% set dataBlocks = form_data_blocks(form) %}

```


'block' - code of block,
If block is not configured in 'block_config'. Block will be created.
If block title is not configured in 'block_config'. Title of block will be same as code.
If form type filed options don't have 'block' attribute, this filed will be ignored

'subblock' - code of subblock,
If subblock is not configured in 'block_config'. SubBlock will be created.
If form type filed options don't have 'subblock' attribute, this field will be added to first subblock in block

If 'subblock' is congigured but 'block' is not configured field will be ignored


'block_config' is optinal attribute
This attribute contain config for block and subblock(title, class, subblocks)