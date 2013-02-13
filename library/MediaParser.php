<?php
	
	/**
	 * This file is part of the PHP Video Toolkit v2 package.
	 *
	 * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
	 * @license Dual licensed under MIT and GPLv2
	 * @copyright Copyright (c) 2008 Oliver Lillie <http://www.buggedcom.co.uk>
	 * @package PHPVideoToolkit V2
	 * @version 2.0.0.a
	 * @uses ffmpeg http://ffmpeg.sourceforge.net/
	 */
	 
	 namespace PHPVideoToolkit;

	/**
	 * This class provides generic data parsing for the output from FFmpeg from specific
	 * media files. Parts of the code borrow heavily from Jorrit Schippers version of 
	 * PHPVideoToolkit v 0.1.9.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class MediaParser extends Parser
	{
		/**
		 * Returns the information about a specific media file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getInformation($file_path, $read_from_cache=true)
		{
			static $file_data = array();
			
// 			check to see if the info has already been generated
		    if($read_from_cache === true && isset($file_data[$file_path]) === true)
			{
		      	return $file_data[$file_path];
		    }
			
//			get the file data
			$data = array(
				'type'  	=> $this->getType($file_path, $read_from_cache),
				'duration'  => $this->getDuration($file_path, $read_from_cache),
				'bitrate'   => $this->getBitrate($file_path, $read_from_cache),
				'start'     => $this->getStart($file_path, $read_from_cache),
				'video' 	=> $this->getVideoComponent($file_path, $read_from_cache),
				'audio' 	=> $this->getAudioComponent($file_path, $read_from_cache),
			);

// 			cache info and return
		    return $file_data[$file_path] = $data;
		}
		
		/**
		 * Returns the files duration as a Timecode object if available otherwise returns false.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the duration is found, otherwise returns null.
		 */
		public function getDuration($file_path, $read_from_cache=true)
		{
			static $file_data = array();
			
// 			check to see if the info has already been generated
		    if($read_from_cache === true && isset($file_data[$file_path]) === true)
			{
		      	return $file_data[$file_path];
		    }
			
//			get the raw data
			$raw_data = $this->getRawInformation($file_path, $read_from_cache);
			
// 			grab the duration
			$data = null;
			if(preg_match('/Duration: ([^,]*)/', $raw_data, $matches) > 0)
			{
				$data = new Timecode($matches[1], Timecode::INPUT_FORMAT_TIMECODE, '%hh:%mm:%ss.%ms');
			}

			return $file_data[$file_path] = $data;
		}
		
		/**
		 * Returns the files bitrate if available otherwise returns -1.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns the bitrate as an integer if available otherwise returns -1.
		 */
		public function getBitrate($file_path, $read_from_cache=true)
		{
			static $file_data = array();
			
// 			check to see if the info has already been generated
		    if($read_from_cache === true && isset($file_data[$file_path]) === true)
			{
		      	return $file_data[$file_path];
		    }
			
//			get the raw data
			$raw_data = $this->getRawInformation($file_path, $read_from_cache);
			
// 			grab the bitrate
			$data = null;
			if(preg_match('/bitrate: ([^,]*)/', $raw_data, $matches) > 0)
			{
				$data = strtoupper($value) === 'N/A' ? -1 : (int) $matches[1];
			}

			return $file_data[$file_path] = $data;
		}
		
		/**
		 * Returns the start point of the file as a Timecode object if available, otherwise returns null.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the start point is found, otherwise returns null.
		 */
		public function getStart($file_path, $read_from_cache=true)
		{
			static $file_data = array();
			
// 			check to see if the info has already been generated
		    if($read_from_cache === true && isset($file_data[$file_path]) === true)
			{
		      	return $file_data[$file_path];
		    }
			
//			get the raw data
			$raw_data = $this->getRawInformation($file_path, $read_from_cache);
			
// 			grab the bitrate
			$data = null;
			if(preg_match('/start: ([^,]*)/', $raw_data, $matches) > 0)
			{
				$data = new Timecode($value, Timecode::INPUT_FORMAT_SECONDS);
			}

			return $file_data[$file_path] = $data;
		}
		
		/**
		 * Returns the start point of the file as a Timecode object if available, otherwise returns null.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a string 'audio' or 'video' if media is audio or video, otherwise returns null.
		 */
		public function getType($file_path, $read_from_cache=true)
		{
			static $file_data = array();
			
// 			check to see if the info has already been generated
		    if($read_from_cache === true && isset($file_data[$file_path]) === true)
			{
		      	return $file_data[$file_path];
		    }
			
//			get the raw data
			$raw_data = $this->getRawInformation($file_path, $read_from_cache);

// 			grab the type
			$data = null;
			if(preg_match('/Stream.*: Video: .*/', $raw_data, $matches) > 0)
			{
//				special check to see if the file is actually an image and not a video.
				if(getimagesize($file_path) !== false)
				{
					$data = 'image';
				}
				else
				{
					$data = 'video';
				}
			}
			else if(preg_match('/Stream.*: Audio: .*/', $raw_data, $matches) > 0)
			{
				$data = 'audio';
			}
			else
			{
				$data = null;
			}

			return $file_data[$file_path] = $data;
		}
		
		/**
		 * Returns any video information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		public function getVideoComponent($file_path, $read_from_cache=true)
		{
			static $file_data = array();
			
// 			check to see if the info has already been generated
		    if($read_from_cache === true && isset($file_data[$file_path]) === true)
			{
		      	return $file_data[$file_path];
		    }
			
//			get the raw data
			$raw_data = $this->getRawInformation($file_path, $read_from_cache);

// 			match the video stream info
			$data = null;
			if(preg_match('/Stream(.*): Video: (.*)/', $raw_data, $matches) > 0)
			{
				$data = array(
					'dimensions' => array(
						'width' => null,
						'height' => null,
					),
					'time_bases' => array(),
					'frame_rate' => null,
					'frame_count' => null,
					'pixel_aspect_ratio' => null,
					'display_aspect_ratio' => null,
					'pixel_format' => null,
					'codec' => null,
					'metadata' => array(),
				);

// 				get the dimension parts
				if(preg_match('/([1-9][0-9]*)x([1-9][0-9]*)/', $matches[2], $dimensions_matches) > 0)
				{
					$data['dimensions'] = array(
						'width' => (float) $dimensions_matches[1],
						'height' => (float) $dimensions_matches[2],
					);
				}
				$dimension_match = $dimensions_matches[0];

// 				get the timebases
				$data['time_bases'] = array();
				if(preg_match_all('/([0-9\.k]+) (fps|tbr|tbc|tbn)/', $matches[0], $timebase_matches) > 0)
				{
					foreach ($timebase_matches[2] as $key => $abrv)
					{
						$data['time_bases'][$abrv] = $timebase_matches[1][$key];
					}
				}
				$timebase_match = implode(', ', $timebase_matches[0]);
			
// 				get the video frames per second
				$fps = isset($data['time_bases']['fps']) === true ? $data['time_bases']['fps'] : 
					  (isset($data['time_bases']['tbr']) === true ? $data['time_bases']['tbr'] : 
				  	   false);
				if ($fps !== false)
				{
					$data['frame_rate'] = (float) $fps;
					$data['frame_count'] = ceil($data['duration']->seconds * $data['frame_rate']);
				}

// 				get the ratios
				if(preg_match('/\[PAR|SAR ([0-9\:\.]+) DAR ([0-9\:\.]+)\]/', $matches[0], $ratio_matches) > 0)
				{
					$data['pixel_aspect_ratio'] = $ratio_matches[1];
					$data['display_aspect_ratio'] = $ratio_matches[2];
				}
				
// 				formats should be anything left over, let me know if anything else exists
				$parts = explode(',', $matches[2]);
				$other_parts = array($dimension_match, $timebase_match);
				$formats = array();
				foreach ($parts as $key => $part)
				{
					$part = trim($part);
					if(in_array($part, $other_parts) === false)
					{
						array_push($formats, $part);
					}
				}
				$data['pixel_format'] = $formats[1];
				$data['codec'] = $formats[0];
				
//				get metadata from the video input, (if any)
				$meta_data_search_from = strpos($raw_data, $matches[0]);
				$meta_data_search = trim(substr($raw_data, $meta_data_search_from+strlen($matches[0])));
				if(strpos($meta_data_search, 'Metadata:') === 0 && preg_match('/Metadata:(.*)Stream/ms', $meta_data_search, $meta_matches) > 0)
				{
					if(preg_match_all('/([a-z\_]+)\s+\: (.*)/', $meta_matches[1], $meta_matches) > 0)
					{
						foreach ($meta_matches[2] as $key => $value)
						{
							$data['metadata'][$meta_matches[1][$key]] = $value;
						}
					}
				}
			}
			
			return $file_data[$file_path] = $data;
		}
		
		/**
	 	 * Returns any audio information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		public function getAudioComponent($file_path, $read_from_cache=true)
		{
			static $file_data = array();
			
// 			check to see if the info has already been generated
		    if($read_from_cache === true && isset($file_data[$file_path]) === true)
			{
		      	return $file_data[$file_path];
		    }
			
//			get the raw data
			$raw_data = $this->getRawInformation($file_path, $read_from_cache);
			
// 			match the audio stream info
			$data = null;
			if(preg_match('/Stream(.*): Audio: (.*)/', $raw_data, $matches) > 0)
			{
				$data = array(
					'stereo' 		=> null,
					'channels' 		=> null,
					'sample_rate' 	=> null,
					'bitrate' 		=> null,
					'metadata' 		=> array(),
				);
				
				$other_parts = array();
				
// 				get the stereo value
				if(preg_match('/(stereo|mono)/i', $matches[0], $stereo_matches) > 0)
				{
					$data['stereo'] = $stereo_matches[0];
					$data['channels'] = $stereo_matches[0] === 'mono' ? 1 : ($stereo_matches[0] === 'stereo' ? 2 : ($stereo_matches[0] === '5.1' ? 6 : 0));
					array_push($other_parts, $stereo_matches[0]);
				}
				
// 				get the sample_rate
				if(preg_match('/([0-9]{3,6}) Hz/', $matches[0], $sample_matches) > 0)
				{
					$data['sample_rate'] = (float) $sample_matches[1];
					array_push($other_parts, $sample_matches[0]);
				}

// 				get the bit rate
				if(preg_match('/([0-9]{1,3}) kb\/s/', $matches[0], $bitrate_matches) > 0)
				{
					$data['bitrate'] = (float) $bitrate_matches[1];
					array_push($other_parts, $bitrate_matches[0]);
				}

// 				formats should be anything left over, let me know if anything else exists
				$parts = explode(',', $matches[2]);
				$formats = array();
				foreach ($parts as $key => $part)
				{
					$part = trim($part);
					if(in_array($part, $other_parts) === false)
					{
						array_push($formats, $part);
					}
				}
				$data['codec'] = $formats[0];
				
//				get metadata from the audio input, (if any)
//				however if we have a video source in the media it is outputted differently than just pure audio.
				if(strpos($raw_data, 'Metadata:') !== false)
				{
					if(preg_match('/Metadata:(.*)(?:Stream|At least)/ms', $meta_data_search, $meta_matches) > 0)
					{
						if(preg_match_all('/([a-z\_]+)\s+\: (.*)/', $meta_matches[1], $meta_matches) > 0)
						{
							foreach ($meta_matches[2] as $key => $value)
							{
								$data['metadata'][$meta_matches[1][$key]] = $value;
							}
						}
					}
//					this is just pure audio and is essnetially id3 data.
					else if(preg_match('/Metadata:(.*)(?:Duration)/ms', $raw_data, $meta_matches) > 0)
					{
						if(preg_match_all('/([a-z\_]+)\s+\: (.*)/', $meta_matches[1], $meta_matches) > 0)
						{
							foreach ($meta_matches[2] as $key => $value)
							{
								$data['metadata'][$meta_matches[1][$key]] = $value;
							}
						}
					}
				}
			}
			
			return $file_data[$file_path] = $data;
		}
		
		/**
		 * Returns a boolean value determined by the media having an audio channel.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return boolean
		 */
		public function hasAudio($file_path, $read_from_cache=true)
		{
			static $file_data = array();
			
// 			check to see if the info has already been generated
		    if($read_from_cache === true && isset($file_data[$file_path]) === true)
			{
		      	return $file_data[$file_path];
		    }
			
//			get the raw data
			$raw_data = $this->getRawInformation($file_path, $read_from_cache);
			
// 			match the audio stream info
			$data = false;
			if(preg_match('/Stream.+Audio/', $raw_data, $matches) > 0)
			{
				$data = true;
			}
			
			return $data;
		}
		
		/**
		 * Returns a boolean value determined by the media having a video channel.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return boolean
		 */
		public function hasVideo($file_path, $read_from_cache=true)
		{
			static $file_data = array();
			
// 			check to see if the info has already been generated
		    if($read_from_cache === true && isset($file_data[$file_path]) === true)
			{
		      	return $file_data[$file_path];
		    }
			
//			get the raw data
			$raw_data = $this->getRawInformation($file_path, $read_from_cache);
			
// 			match the audio stream info
			$data = false;
			if(preg_match('/Stream.+Video/', $raw_data, $matches) > 0)
			{
				$data = true;
			}
			
			return $data;
		}
		
		/**
		 * Returns the raw data provided by ffmpeg about a file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns false if no data is returned, otherwise returns the raw data as a string.
		 */
		public function getRawInformation($file_path, $read_from_cache=true)
		{
//			convert to realpath
			$real_file_path = realpath($file_path);

//			validate the file exists and is readable.
			if(is_file($real_file_path) === false)
			{
				throw new Exception('The file "'.$file_path.'" cannot be found in \\PHPVideoToolkit\\DataParserAbstract::fileInformation.');
			}
			else if(is_readable($real_file_path) === false)
			{
				throw new Exception('The file "'.$file_path.'" is not readable in \\PHPVideoToolkit\\DataParserAbstract::fileInformation.');
			}

			static $file_info = array();
			
// 			check to see if the info has already been generated
			$hash = md5_file($real_file_path).'_'.filemtime($real_file_path);
		    if($read_from_cache === true && isset($file_info[$hash]) === true)
			{
		      	return $file_info[$hash];
		    }

// 			execute the ffmpeg lookup
			$exec = new ExecBuffer($this->_ffmpeg_path, $this->_temp_directory);
			$raw_data = $exec->setInput($real_file_path)
							 ->execute();
			
// 			check that some data has been obtained
			$data = array();
		    if(empty($raw_data) === true)
			{
				// TODO possible error/exception here.
				return $file_info[$hash] = false;
		    }
			
			return $file_info[$hash] = implode("\n", $raw_data);
		}
	}