#!/usr/bin/env php
<?php
/**
 * File containing the bcsubtreecopychildrenretainsections.php script.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @copyright Copyright (C) 1999-2015 Brookins Consulting. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package bcsubtreecopyretainsections
 */

// script initializing
require 'autoload.php';

$cli = eZCLI::instance();

// START EDIT BC: bcsubtreecopychildrenretainsections
$script = eZScript::instance( array( 'description' => ( "\n" .
                                                        "This script will make a copy of a content object subtree children and place it in a specified\n" .
                                                        "location. Will optionally retain exiting content object section assignments.\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true,
                                     'user' => true ) );
// END EDIT BC: bcsubtreecopychildrenretainsections
$script->startup();

// START EDIT BC: bcsubtreecopyretainsections
$scriptOptions = $script->getOptions( "[src-node-id:][dst-node-id:][all-versions][keep-creator][keep-time][retain-sections]",
                                      "",
                                      array( 'src-node-id' => "Source subtree parent node ID.",
                                             'dst-node-id' => "Destination node ID.",
                                             'all-versions' => "Copy all versions for each contentobject being copied.",
                                             'keep-creator'=> "Do not change the creator (user) for the copied content objects.",
                                             'keep-time'   => "Do not change the creation and modification time of the copied content objects.",
                                             'retain-sections'   => "Do not change the object section assignments of the copied content objects."
                                             ),
                                      false,
                                      array( 'user' => true )
                                     );
$script->initialize();

$srcNodeID   = $scriptOptions[ 'src-node-id' ] ? $scriptOptions[ 'src-node-id' ] : false;
$dstNodeID   = $scriptOptions[ 'dst-node-id' ] ? $scriptOptions[ 'dst-node-id' ] : false;
$allVersions = $scriptOptions[ 'all-versions' ];
$keepCreator = $scriptOptions[ 'keep-creator' ];
$keepTime    = $scriptOptions[ 'keep-time' ];
// END EDIT BC: bcsubtreecopyretainsections

// START EDIT BC: bcsubtreecopychildrenretainsections
$siteaccess = isset( $scriptOptions[ 'siteaccess' ] ) ? true : false;
$login = isset( $scriptOptions[ 'login' ] ) ? true : false;
$password = isset( $scriptOptions[ 'password' ] ) ? true : false;
$retainSections = isset( $scriptOptions[ 'retain-sections' ] ) ? true : false;

$siteaccessCommandlineOption = '';
$loginCommandlineOption = '';
$passwordCommandlineOption = '';
$retainSectionsCommandlineOption = '';
$allVersionsCommandlineOption = '';
$keepCreatorCommandlineOption = '';
$keepTimeCommandlineOption = '';

if( $siteaccess )
{
    $siteaccessCommandlineOption = " -s " . $scriptOptions['siteaccess'];
}

if( $login )
{
    $loginCommandlineOption = " -l " . $scriptOptions['login'];
}

if( $password )
{
    $passwordCommandlineOption = " -p " . $scriptOptions['password'];
}

if( $retainSections )
{
    $retainSectionsCommandlineOption = " -retain-sections";
}

if( $allVersions )
{
    $allVersionsCommandlineOption = " --all-versions";
}

if( $keepCreator )
{
    $keepCreatorCommandlineOption = " --keep-creator";
}

if( $keepTime )
{
    $keepTimeCommandlineOption = " --keep-time";
}

$commandlineOptionsPre = $siteaccessCommandlineOption . $loginCommandlineOption . $passwordCommandlineOption . $retainSectionsCommandlineOption;
$commandlineOptionsPost = $allVersionsCommandlineOption . $keepCreatorCommandlineOption . $keepTimeCommandlineOption;

$sourceNodeList    = array();
$syncNodeIDListSrc = array();

$sourceNodeList = eZContentObjectTreeNode::subTreeByNodeID( false, $srcNodeID );
$countNodeList = count( $sourceNodeList );

// Prepare list of source node IDs. We will need it in the future
// for checking node is inside or outside of the subtree being copied.
$sourceNodeIDList = array();
foreach ( $sourceNodeList as $sourceNode )
    $sourceNodeIDList[] = $sourceNode->attribute( 'node_id' );

$sourceNodeIDListOriginalCount = count( $sourceNodeIDList );

$k = 0;
while ( count( $sourceNodeIDList ) > 0 )
{
    if ( $k > $countNodeList )
    {
        $cli->error( "Subtree Copy Error!\nToo many loops while copying nodes." );
        $script->shutdown( 6 );
    }

    for ( $i = 0; $i < count( $sourceNodeIDList ); $i)
    {
        $sourceNodeID = $sourceNodeIDList[ $i ];

        $cli->output( system( "php -d memory_limit=-1 ./extension/bcsubtreecopyretainsections/bin/php/bcsubtreecopyretainsections.php$commandlineOptionsPre --src-node-id=$sourceNodeID --dst-node-id=$dstNodeID$commandlineOptionsPost"), false );

        // if copying successful then remove $sourceNode from $sourceNodeIDList
        array_splice( $sourceNodeIDList, $i, 1 );
        $i++;
    }
    $k++;
}

$cli->output( "\nEstimated number of copied nodes: " . $sourceNodeIDListOriginalCount . "\n" );

$cli->output( "bcsubtreecopychildrenretainsections : Done." );

$script->shutdown();

// END EDIT BC: bcsubtreecopychildrenretainsections

?>