<?php

class Services_Providers_Nzb {
	private $_cacheDao;
	private $_nntpSpotReading;

	/*
	 * constructor
	 */
	public function __construct(Dao_Cache $cacheDao, Services_Nntp_SpotReading $nntpSpotReading) {
		$this->_cacheDao = $cacheDao;
		$this->_nntpSpotReading = $nntpSpotReading;
	}  # ctor
	
	/* 
	 * Geef de NZB file terug
	 */
	function fetchNzb($fullSpot) {
		SpotTiming::start(__FUNCTION__);

		/*
		 * Retrieve the NZB from the cache
		 */
		$nzb = $this->_cacheDao->getCachedNzb($fullSpot['messageid']);
		if (!empty($nzb)) {
			/*
			 * NZB file is alread in the cache, update the cache timestamp
			 */
			$nzb = $nzb['content'];
			$this->_cacheDao->updateNzbCacheStamp($fullSpot['messageid']);
		} else {
			/*
			 * File is not in the cache yet, retrieve it from the appropriate store, and
			 * store it in the cache
			 */
            $nzb = null;

            // Search for alternate download urls
            $alternateDownload = new SpotAlternateDownload($fullSpot);

            // Only return an alternate if there is one.
            if ($alternateDownload->hasNzb()) {
                $nzb = $alternateDownload->getNzb();
            } else {
                $nzb = $this->_nntpSpotReading->readBinary($fullSpot['nzb'], true);
            } # else

			$this->_cacheDao->saveNzbCache($fullSpot['messageid'], $nzb);
		} # else

		SpotTiming::stop(__FUNCTION__, array($fullSpot));

		return $nzb;
	} # fetchNzb
	

} # Services_Providers_Nzb