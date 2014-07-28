<?php
abstract class FetchTweets_AdminPage_ extends FetchTweets_AdminPage_AddRuleByList {
		
	/*
	 * Layout the setting pages
	 * */
	public function head_FetchTweets_AdminPage( $strHead ) {

		$oUserAds = isset( $GLOBALS['oFetchTweetsUserAds'] ) ? $GLOBALS['oFetchTweetsUserAds'] : new FetchTweets_UserAds;
		return $strHead 
			. '<div class="fetch-tweets-admin-body">'
			. '<table border="0" cellpadding="0" cellspacing="0" unselectable="on" width="100%">
			<tbody>
			<tr>
			<td valign="top">'
			. '<div style="margin-top: 10px;">'		
			. $oUserAds->getTextAd()
			. '</div>';
			
	}
	public function foot_FetchTweets_AdminPage( $strFoot ) {
		
		switch ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'authentication' ) {
			case 'authentication':
				$numItems = defined( 'WPLANG' ) && WPLANG == 'ja' ? 4 : 4;
				break;
			default:
				$numItems = 4;
				break;
		}	
		
		$oUserAds = isset( $GLOBALS['oFetchTweetsUserAds'] ) ? $GLOBALS['oFetchTweetsUserAds'] : new FetchTweets_UserAds;
		return $strFoot 
			// . '<div style="float:left; margin-top: 10px" >' 
			// . $oUserAds->getTextAd() 
			// . '</div>'
			. '</td>
			<td valign="top" rowspan="2" style="padding-top:20px;">' 
			. ( rand( 0, 1 ) ? $oUserAds->get160xNTopRight() : $oUserAds->get160xN( $numItems ) )
			// . $this->oUserAds->GetSkyscraper( $numItems ) 
			. '</td>
			</tr>
			<tr>
				<td valign="bottom" align="center">'
			// . $oUserAds->getBottomBanner() 
			. '</td>
			</tr>
			</tbody>
			</table>'
			. '</div><!-- end fetch-tweets-admin-body -->';
			
	}	 
		
}