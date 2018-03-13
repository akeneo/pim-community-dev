module.exports = (
    identifier,
    code,
    labels = { en_US: '', fr_FR: '', de_DE: '' },
    updatedDate = '03/07/2018 09:35 AM'
) => {
    return {
        'code': code,
        'labels': {
            'en_US': labels.en_US,
            'fr_FR': labels.fr_FR
        },
        'meta': {
            'id': 1,
            'form': 'pim-association-type-edit-form',
            'model_type': 'association_type',
            'created': {
                'id': identifier,
                'author': 'system - Removed user',
                'resource_id': '1',
                'snapshot': {
                    'code': code,
                    'label-en_US': labels.en_US,
                    'label-fr_FR': labels.fr_FR
                },
                'changeset': {
                    'code': {
                        'old': '',
                        'new': code
                    },
                    'label-en_US': {
                        'old': '',
                        'new': labels.en_US
                    },
                    'label-fr_FR': {
                        'old': '',
                        'new': labels.fr_FR
                    }
                },
                'context': null,
                'version': 1,
                'logged_at': updatedDate,
                'pending': false
            },
            'updated': {
                'id': identifier,
                'author': 'system - Removed user',
                'resource_id': '1',
                'snapshot': {
                    'code': code,
                    'label-en_US': labels.en_US,
                    'label-fr_FR': labels.fr_FR
                },
                'changeset': {
                    'code': {
                        'old': '',
                        'new': code
                    },
                    'label-en_US': {
                        'old': '',
                        'new': labels.en_US
                    },
                    'label-fr_FR': {
                        'old': '',
                        'new': labels.fr_FR
                    }
                },
                'context': null,
                'version': 1,
                'logged_at': updatedDate,
                'pending': false
            }
        }
    };
};
