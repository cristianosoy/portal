<?php
/**
 * de_DE
 *
 * German message token translations for the 'portal' sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Kai Schröer (https://schroeer.co)
 *
 */

return [
    'COOKIES' => [
        'MESSAGE'                   => 'Diese Webseite verwendet Cookies, um in vollem Umfang funktionieren zu können.',
        'DISMISS'                   => 'Verstanden!',
        'LINK'                      => 'Mehr erfahren',
        'URL'                       => 'https://cookieconsent.insites.com/'
    ],
    'APPLICATION' => [
        1                           => 'Bewerbung',
        2                           => 'Bewerbungen',
        'DESCRIPTION'               => 'Verfassen Sie Ihre Bewerbung für den Hackathon und senden Sie diese zur Prüfung an das Team.',
        'PAGE_DESCRIPTION'          => 'Eine Liste der Bewerbungen für Ihr Event. Bietet Verwaltungstools für das Bearbeiten und Löschen von Bewerbungen',
        'CREATED'                   => 'Bewerbung erfolgreich abgeschickt',
        'UPDATED'                   => 'Bewerbung aktualisiert',
        'DELETED'                   => 'Bewerbung gelöscht',
        'DELETE_CONFIRM'            => 'Sind Sie sicher, dass Sie die Bewerbung löschen möchten?',
        'DELETE_YES'                => 'Ja, Bewerbung löschen',
        'PERSONAL_INFO'             => 'Über dich (* = Pflichtfelder)',
        'FULL_NAME'                 => 'Name',
        'EMAIL'                     => 'Email',
        'UNIVERSITY'                => 'Universität (wird an Hand der Email beim Speichern gefüllt)',
        'BIRTHDAY'                  => 'Geburtstag',
        'PHONE'                     => 'Telefonnummer',
        'STREET'                    => 'Straße',
        'POSTAL_CODE'               => 'PLZ',
        'CITY'                      => 'Stadt',
        'STATE'                     => 'Bundesland',
        'ADDITIONAL'                => 'Weitere Angaben zu dir',
        'REFERRER'                  => 'Wie hast du von unserem Event erfahren?',
        'DIETARY_RESTRICTIONS'      => 'Besonderheiten bei der Ernährung',
        'COMMENT'                   => 'Ein paar Informationen zu dir. Was hast du bisher gemacht? :)',
        'TEAM'                      => 'Informationen zum Team',
        'TEAMMATE'                  => 'Teamkollege',
        'SUBMIT'                    => 'Abschicken',
        'TOS_ACCEPTED'              => 'Ich akzeptiere die <a {{link_attributes | raw}}>Nutzungsbedingungen</a>.',
        'TOS_FOR'                   => 'Mit der Teilnahme am {{title}} {{year}} stimmt der Teilnehmer folgendem zu:',
        'TOS'                       => '<p>1. Der Teilnehmer erlaubt die Aufnahme und Verwendung von Fotos und Videos während der Veranstaltung und danach.</p>'.
                                       '<p>2. Der Teilnehmer verpflichtet sich zum Einhalt des <a href="http://berlincodeofconduct.org/" target="_blank">Berlin Code of Conduct</a>.</p>'.
                                       '<p>3. Der Teilnehmer verpflichtet sich jegliche Schäden, die durch das angebotene, öffentliche WLAN-Netzwerk entstehen, selbst zu tragen.</p>'.
                                       '<p>4. Der Teilnehmer stimmt der Speicherung seiner personenbezogenen Daten, soweit dies im Sinne dieser und zukünftiger Veranstaltungen ist, zu. Diese Zustimmung ist freiwillig und widerruflich. Im Falle des Widerrufes nach der Veranstaltung erfolgt eine Löschung.</p>',
        'CLOSE'                     => 'Akzeptieren',
        'CLOSED'                    => 'Wir können leider keine weiteren Bewerbungen annehmen.',
        'VIEW'                      => 'Anzeigen',
        'ACCEPT'                    => 'Akzeptieren',
        'REJECT'                    => 'Ablehnen',
        'STATUS_REVIEWING'          => 'Deine Bewerbung befindet sich in Bearbeitung',
        'STATUS_ACCEPTED'           => 'Deine Bewerbung wurde akzeptiert'
    ],
    'COUNTRY' => [
        1                           => 'Land',
        2                           => 'Länder',
        'PAGE_DESCRIPTION'          => 'Eine Liste der Länder. Bietet Verwaltungstools für das Bearbeiten und Löschen von Länder',
        'CODE'                      => 'Eindeutiger Code',
        'CREATE'                    => 'Land erstellen',
        'NAME_IN_USE'               => 'Es existiert bereits ein Land mit dem Namen <strong>{{name}}</strong>',
        'CODE_IN_USE'               => 'Es existiert bereits ein Land mit dem Code <strong>{{code}}</strong>',
        'CREATION_SUCCESSFUL'       => 'Das Land <strong>{{name}}</strong> wurde erfolgreich erstellt',
        'UPDATED'                   => 'Details für das Land <strong>{{name}}</strong> aktualisiert',
        'DELETE_CONFIRM'            => 'Sind Sie sicher, dass Sie das Land <strong>{{name}}</strong> löschen möchten?',
        'DELETE_YES'                => 'Ja, Land löschen',
        'DELETION_SUCCESSFUL'       => 'Das Land <strong>{{name}}</strong> wurde erfolgreich gelöscht'
    ],
    'EXPERTISE' => [
        1                           => 'Fachgebiet',
        2                           => 'Fachgebiete',
        'PAGE_DESCRIPTION'          => 'Eine Liste der Fachgebiete. Bietet Verwaltungstools für das Bearbeiten und Löschen von Fachgebiete',
        'CREATE'                    => 'Fachgebiet erstellen',
        'NAME_IN_USE'               => 'Es existiert bereits ein Fachgebiet mit dem Namen <strong>{{name}}</strong>',
        'CREATION_SUCCESSFUL'       => 'Das Fachgebiet <strong>{{name}}</strong> wurde erfolgreich erstellt',
        'UPDATED'                   => 'Details für das Fachgebiet <strong>{{name}}</strong> aktualisiert',
        'DELETE_CONFIRM'            => 'Sind Sie sicher, dass Sie das Fachgebiet <strong>{{name}}</strong> löschen möchten?',
        'DELETE_YES'                => 'Ja, Fachgebiet löschen',
        'DELETION_SUCCESSFUL'       => 'Das Fachgebiet <strong>{{name}}</strong> wurde erfolgreich gelöscht'
    ],
    'UNIVERSITY' => [
        1                           => 'Universität',
        2                           => 'Universitäten',
        'PAGE_DESCRIPTION'          => 'Eine Liste der Universitäten. Bietet Verwaltungstools für das Bearbeiten und Löschen von Universitäten',
        'DOMAIN'                    => 'Domain',
        'CREATE'                    => 'Universität erstellen',
        'NAME_IN_USE'               => 'Es existiert bereits eine Universität mit dem Namen <strong>{{name}}</strong>',
        'DOMAIN_IN_USE'             => 'Es existiert bereits eine Universität mit dieser Domain <strong>{{domain}}</strong>',
        'CREATION_SUCCESSFUL'       => 'Die Universität <strong>{{name}}</strong> wurde erfolgreich erstellt',
        'UPDATED'                   => 'Details für die Universität <strong>{{name}}</strong> aktualisiert',
        'DELETE_CONFIRM'            => 'Sind Sie sicher, dass Sie die Universität <strong>{{name}}</strong> löschen möchten?',
        'DELETE_YES'                => 'Ja, Universität löschen',
        'DELETION_SUCCESSFUL'       => 'Die Universität <strong>{{name}}</strong> wurde erfolgreich gelöscht'
    ],
    'EMAIL' => [
        'VERIFICATION_REQUIRED'     => 'E-Mail (Bestätigung benötigt - Benutzen Sie eine universitäts E-Mail Adresse!)'
    ],
    'REGISTER' => [
        '@TRANSLATION'              => 'Registrieren',
        'INFO'                      => 'Nach Ihrer erfolgreichen Registrierung können Sie sich für {{site_title}} bewerben. Bitte benutzen Sie eine gültige universitäts E-Mail Adresse, wir werden dies bei der Bewerbung überprüfen.'
    ]
];
