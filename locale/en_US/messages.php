<?php
/**
 * en_US
 *
 * US English message token translations for the 'portal' sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Kai Schröer (https://schroeer.co)
 *
 */

return [
    'COOKIES' => [
        'MESSAGE'                   => 'This website uses cookies to ensure you get the best experience on our website.',
        'DISMISS'                   => 'Got it!',
        'LINK'                      => 'Learn more',
        'URL'                       => 'https://cookieconsent.insites.com/'
    ],
    'APPLICATION' => [
        1                           => 'Application',
        2                           => 'Applications',
        'DESCRIPTION'               => 'Create your application for the hackathon and let it be checked by the team.',
        'PAGE_DESCRIPTION'          => 'A listing of the applications for your event. Provides management tools for editing and deleting applications',
        'CREATED'                   => 'Application successfully created',
        'UPDATED'                   => 'Application updated',
        'DELETED'                   => 'Application deleted',
        'DELETE_CONFIRM'            => 'Are you sure you want to delete the application?',
        'DELETE_YES'                => 'Yes, delete application',
        'PERSONAL_INFO'             => 'Personal information (* = required)',
        'FULL_NAME'                 => 'Full Name',
        'EMAIL'                     => 'Email',
        'UNIVERSITY'                => 'University (detected from your email while saving)',
        'BIRTHDAY'                  => 'Birthday',
        'PHONE'                     => 'Telephone number',
        'STREET'                    => 'Street',
        'POSTAL_CODE'               => 'Postal code',
        'CITY'                      => 'City',
        'STATE'                     => 'State',
        'ADDITIONAL'                => 'Additional information',
        'REFERRER'                  => 'How did you hear about our event?',
        'DIETARY_RESTRICTIONS'      => 'Dietary restrictions',
        'COMMENT'                   => 'Tell us about you and your awesome projects so far :)',
        'TEAM'                      => 'Team information',
        'TEAMMATE'                  => 'Teammate',
        'SUBMIT'                    => 'Submit',
        'TOS_ACCEPTED'              => 'I accept the <a {{link_attributes | raw}}>legal terms</a>.',
        'TOS_FOR'                   => 'By attending {{title}} {{year}} the participant agrees to the following terms:',
        'TOS'                       => '<p>1. The attendee permits the production and use of photos and videos during and after the event.</p>'.
                                       '<p>2. The attendee commits himself to act according to the <a href="http://berlincodeofconduct.org/" target="_blank">Berlin Code of Conduct</a>.</p>'.
                                       '<p>3. The attendee commits himself to wear all damages that may be caused by the provided free WLAN Network by himself.</p>'.
                                       '<p>4. The attendee allows the processing and storage of personal data as far as it is necessary to carry out this or future events. This agreement is voluntary and revocable. In case of revocation after the event the data will be deleted.</p>',
        'CLOSE'                     => 'Got it',
        'CLOSED'                    => 'Sorry, but we can´t accept any more applications.',
        'VIEW'                      => 'View',
        'ACCEPT'                    => 'Accept',
        'REJECT'                    => 'Reject',
        'STATUS_REVIEWING'          => 'Your application is being reviewed',
        'STATUS_ACCEPTED'           => 'Your application got accepted'
    ],
    'COUNTRY' => [
        1                           => 'Country',
        2                           => 'Countries',
        'PAGE_DESCRIPTION'          => 'A listing of countries. Provides management tools for editing and deleting countries',
        'CODE'                      => 'Unique Code',
        'CREATE'                    => 'Create country',
        'NAME_IN_USE'               => 'A country named <strong>{{name}}</strong> already exists',
        'CODE_IN_USE'               => 'A country with the code <strong>{{code}}</strong> already exists',
        'CREATION_SUCCESSFUL'       => 'Successfully created country <strong>{{name}}</strong>',
        'UPDATED'                   => 'Details updated for country <strong>{{name}}</strong>',
        'DELETE_CONFIRM'            => 'Are you sure you want to delete the country <strong>{{name}}</strong>?',
        'DELETE_YES'                => 'Yes, delete country',
        'DELETION_SUCCESSFUL'       => 'Successfully deleted country <strong>{{name}}</strong>'
    ],
    'EXPERTISE' => [
        1                           => 'Expertise',
        2                           => 'Expertises',
        'PAGE_DESCRIPTION'          => 'A listing of expertises. Provides management tools for editing and deleting expertises',
        'CREATE'                    => 'Create expertise',
        'NAME_IN_USE'               => 'An expertise named <strong>{{name}}</strong> already exists',
        'CREATION_SUCCESSFUL'       => 'Successfully created expertise <strong>{{name}}</strong>',
        'UPDATED'                   => 'Details updated for expertise <strong>{{name}}</strong>',
        'DELETE_CONFIRM'            => 'Are you sure you want to delete the expertise <strong>{{name}}</strong>?',
        'DELETE_YES'                => 'Yes, delete expertise',
        'DELETION_SUCCESSFUL'       => 'Successfully deleted expertise <strong>{{name}}</strong>'
    ],
    'UNIVERSITY' => [
        1                           => 'University',
        2                           => 'Universities',
        'PAGE_DESCRIPTION'          => 'A listing of universities. Provides management tools for editing and deleting universities',
        'DOMAIN'                    => 'Domain',
        'CREATE'                    => 'Create university',
        'NAME_IN_USE'               => 'An university named <strong>{{name}}</strong> already exists',
        'DOMAIN_IN_USE'             => 'An university with domain <strong>{{domain}}</strong> already exists',
        'CREATION_SUCCESSFUL'       => 'Successfully deleted university <strong>{{name}}</strong>',
        'UPDATED'                   => 'Details updated for university <strong>{{name}}</strong>',
        'DELETE_CONFIRM'            => 'Are you sure you want to delete the university <strong>{{name}}</strong>?',
        'DELETE_YES'                => 'Yes, delete university',
        'DELETION_SUCCESSFUL'       => 'Successfully deleted university <strong>{{name}}</strong>'
    ],
    'EMAIL' => [
        'VERIFICATION_REQUIRED'     => 'Email (verification required - use a university address!)'
    ],
    'REGISTER' => [
        '@TRANSLATION'              => 'Register',
        'INFO'                      => 'After your successful registration you will be able to fill out your application for {{site_title}}. Please use a valid university email for your registration as it will be checked while applying.'
    ]
];
