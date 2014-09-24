<?php
/**
 * Extension array for meta-tag names in HTML5
 * 
 * scheme of the array
 * array(
 *     'names' => array([name1],[name2],...),
 *     [group]    =>  array(
 *         'separator' => [.|:|/|-],
 *         'names'     =>  array(
 *             name1,
 *             name2,
 *             name2,
 *             namen)
 *     )
 * );
 * @todo struct is not good, maybe i have to overthink it again
 * @see http://wiki.whatwg.org/wiki/MetaExtensions
 * @author Ortwin Pinke <ortwin.pinke@conlite.org>
 * 
 * $Id$
 */

return array(
    'names' => array(
        'alexaverifyid',
        'tysontcsverid',
        'apple-itunes-app',
        'baiduspider',
        'citeseerxbot',
        'collection',
        'designer',
        'entity',
        'EssayDirectory',
        'publisher',
        'review_date',
        'es.title',
        'format-detection',
        'verify-v1',
        'googlebot',
        'revisit-after',
        'icbm',
        'HandheldFriendly',
        'markosweb.com/validation',
        'MobileOptimized',
        'itemsPerPage',
        'msvalidate.01',
        'norton-safeweb-site-verification',
        'pinterest',
        'rating',
        'referrer',
        'rights-standard',
        'robots',
        'skype_toolbar',
        'slurp',
        'startIndex',
        'teoma',
        'totalResults',
        'vieport',
        'y_key',
        'yandex-verification',
        'meta_date',
        'Site-Type',
        'wot-verification',
        'mobile-agent'
        ),
    'AGLSTERMS' =>  array(
        'separator' =>  '.',
        'names' =>  array(
            'serviceType',
            'regulation',
            'protectiveMarking',
            'mandate',
            'jurisdiction',
            'isBasedOn',
            'isBasisFor',
            'function',
            'documentType',
            'dateLicensed',
            'category',
            'case',
            'availability',
            'aggregationLevel',
            'act'
        )
    ),
    'apple-mobile-web-app'  =>  array(
        'separator' =>  '-',
        'names'     =>  array(
            'title',
            'status-bar-style',
            'capable'
        )
    ),
    'csrf'  =>  array(
        'separator' =>  '-',
        'names'     =>  array(
            'token',
            'param'
        )  
    ),
    'dc'    =>  array(
        'separator' =>  '.',
        'names'     =>  array(
            'language',
            'date'  =>  array(
                'separator' =>  '.',
                'names'     =>  array(
                    'issued'
                )
            )
        )
    ),
    'dcterms'   =>  array(
        'separator' =>  '.',
        'names'     =>  array(
            'valid',
            'type',
            'title',
            'temporal',
            'tableOfContents',
            'subject',
            'spatial',
            'source',
            'rightsHolder',
            'rights',
            'requires',
            'replaces',
            'relation',
            'references',
            'publisher',
            'provenance',
            'modified',
            'medium',
            'mediator',
            'license',
            'language',
            'isVersionOf',
            'issued',
            'isRequiredBy',
            'isReplacedBy',
            'isReferencedBy',
            'isPartOf',
            'isFormatOf',
            'instructionalMethod',
            'identifier',
            'hasVersion',
            'hasPart',
            'hasFormat',
            'format',
            'extent',
            'educationLevel',
            'description',
            'dateSubmitted',
            'dateCopyrighted',
            'dateAccepted',
            'date',
            'creator',
            'created',
            'coverage',
            'contributor',
            'conformsTo',
            'collection',
            'bibliographicCitation',
            'available',
            'audience',
            'alternative',
            'accrualPolicy',
            'accrualPeriodicity',
            'accrualMethod',
            'accessRights',
            'abstract'            
        )
    ),
    'icas'  =>  array(
        'separator' =>  '.',
        'names'     =>  array(
            'datetime'  =>  array(
                'separator' =>  '.',
                'names'     =>  array(
                    null,
                    'abbr',
                    'day',
                    'long'                    
                )
            )
        )
    ),
    'geo'   =>  array(
        'separator' =>  '.',
        'names'     =>  array(
            'placename',
            'region',
            'lmk',
            'a3',
            'a2',
            'a1',
            'country',
            'position'            
        )
    ),
    'google'    =>  array(
        'separator' =>  '-',
        'names'     =>  array(
            null,
            'translate' =>  array(
                'separator' =>  '-',
                'names'     =>  array(
                    'customization'
                )
            ),
            'site'  =>  array(
                'separator' =>  '-',
                'names'     =>  array(
                    'verification'
                )
            )
        )
    ),
    'msapplication' =>  array(
        'separator' =>  '-',
        'names'     =>  array(
            'TileColor',
            'TileImage',
            'window',
            'navbutton' =>  array(
                'separator' =>  '-',
                'names'     =>  array(
                    'color'
                )
            )
        ),
        'tooltip',
        'starturl',
        'task'
    ),
    'og'    =>  array(
        'separator' =>  ':',
        'names'     =>  array(
            'video',
            'url',
            'type',
            'title',
            'site_name',
            'locale' => array(
                'separator' =>  ':',
                'names'     =>  array(
                    null,
                    'alternate'
                )
            ),
            'image',
            'determiner',
            'description',
            'audio'
        )
    ),
    'wt'    =>  array(
        'separator' =>  '.',
        'names'     =>  array(
            'ti',
            'sv',
            'mc_id',
            'ad',
            'ac',
            'si_x',
            'si_p',
            'si_n',
            'cg_s',
            'cg_n'            
        )
    ),
    'globrix'   =>  array(
        'separator' =>  '.',
        'names'     =>  array(
            'longitude',
            'latitude',
            'priceproximity',
            'underoffer',
            'tenure',
            'poa',
            'period',
            'parking',
            'outsidespace',
            'features',
            'condition',
            'type',
            'bathrooms',
            'bedrooms',
            'postcode',
            'price',
            'instruction'
        )
    ),
    'gwt'   =>  array(
        'separator' =>  ':',
        'names'     =>  array(
            'property'
        )
    ),
    'twitter'   =>  array(
        'separator' => ':',
        'names'     =>  array(
            'card',
            'url',
            'title',
            'description',
            'image',
            'site',
            'creator'
        )
    )
)
?>