<?php
/**
 * @author Luckyboys
 * @since 2016-12-11
 */

function getMinutes( $item )
{
	$minutes = array();
	foreach( $item['time'] as $time )
	{
		$minutes[date( 'i' , $time )] = true;
	}
	
	return implode( ',' , array_keys( $minutes ) );
}

function getHours( $item )
{
	$minutes = array();
	foreach( $item['time'] as $time )
	{
		$minutes[date( 'H' , $time )] = true;
	}
	
	return implode( ',' , array_keys( $minutes ) );
}

$file = $argv[1];
if( empty( $file ) )
{
	return;
}

$startTime = strtotime( $argv[2] );
$endTime = strtotime( $argv[3] );

$counter = array();
$fp = fopen( $file , 'r' );
while( ( $content = fgets( $fp ) ) !== false )
{
	# Dec 11 03:29:01 web10 CROND[29253]: (root) CMD (/usr/local/bin/php /var/www/DressHelper/current/Web/cron.php method=cron.doItPerMinutes inner=1 err=1 >> /tmp/yd_doItPerMinutes)
	preg_match_all( '|(?<date>\w+ \d+ \d+:\d+:\d+) (?:\w+) CROND\[\d+\]: \((?<user>\w+)\) CMD \((?<command>.+?)\)|' , $content , $matches );
	
	foreach( $matches['command'] as $index => $command )
	{
		$date = $matches['date'][$index];
		$time = strtotime( $date );
		if( $startTime > $time || $endTime < $time )
		{
			continue;
		}
		
		if( !isset( $counter[$command] ) )
		{
			$counter[$command] = array(
				'time' => array() ,
				'count' => 0 ,
			);
		}
		$counter[$command]['count'] += 1;
		$counter[$command]['time'][] = $time;
	}
}
fclose( $fp );

/**
 * @param $item
 * @param $command
 */
function _printCommand( $item , $command )
{
	if( $item['count'] % 1440 == 0 )
	{
		printf( "* * * * * %s\n" , $command );
	}
	else if( $item['count'] % 24 == 0 )
	{
		printf( "%s * * * * %s\n" , getMinutes( $item ) , $command );
	}
	else if( $item['count'] > 1 && $item['count'] < 24 )
	{
		printf( "%s %s * * * %s\n" , getMinutes( $item ) , getHours( $item ) , $command );
	}
	else if( $item['count'] == 1 )
	{
		printf( "%d %d * * * %s\n" , date( 'H' , $item['time'][0] ) , date( 'i' , $item['time'][0] ) , $command );
	}
	else
	{
		echo "\t" . $command . "\n";
		print_r( $item );
	}
}

foreach( $counter as $command => $item )
{
	$uniqueCount = count( array_unique( $item['time'] ) );
	$totalCount = count( $item['time'] );
	$times = ceil( $totalCount / $uniqueCount );
	for( $printTimes = 0 ; $printTimes < $times ; $printTimes++ )
	{
		_printCommand( $item , $command );
	}
}