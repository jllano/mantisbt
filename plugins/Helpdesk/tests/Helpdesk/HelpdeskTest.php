<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test cases for Helpdesk requests
 *
 * @package    Tests
 * @subpackage UnitTests
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Includes
 */
require_once dirname( dirname( __FILE__ ) ) . '/TestConfig.php';

# Mantis Core required for class autoloader and constants
require_mantis_core();

/**
 * Test cases for Helpdesk incoming requests
 *
 *
 *
 * @package    Tests
 * @subpackage Issue
 */
class HelpdeskTest extends PHPUnit_Framework_TestCase {
	/**
	 * A test case that tests the following:
	 * 1. Retrieving Issue ID by provided through MailGun `body-plain` parameter
	 * @return void
	 */
	public function testIssueFromMailBody() {
		$t_result_issue_id = helpdesk_issue_from_mail_body( $this->prepareBodyPlainForTest() );

		# Test Issue ID is integer
		$this->assertInternalType( 'int', $t_result_issue_id );

		# Test Issue ID is not zero - 0
		$this->assertGreaterThan( 0, $t_result_issue_id );
	}

	/**
	 * A test case that tests the following:
	 * 1. Retrieve Issue ID by provided through MailGun Recipient body parameter, in different formats
	 *
	 * List of different recipient types:
	 * @example: host+projectID-IssueHash@mantishub.io
	 * @example: host+IssueHash@mantishub.com
	 * @example: host+Username@mantishub.com
	 *
	 * @return void
	 */
	public function testIssueFromRecipient() {
		$t_abort_error = '';
		# Setup recipients in different formats
		$t_recipients = $this->prepareRecipients();

		foreach ( $t_recipients as $t_recipient_email ) {
			# Test recipient was filtered successfully and Issue ID has been retrieved
			$this->assertGreaterThan( 0, helpdesk_issue_from_recipient( $t_recipient_email, $t_abort_error ) );
		}
	}

	/**
	 * A test case that tests the following:
	 * 1. Construct mail rollback Issue-Hash signature for each Helpdesk message being sent
	 * 2. # Issue and # Hash tags are being set
	 * 3. Cut and test # Hash tag and it's value
	 * @return void
	 */
	public function testIssueNoteHashSignature() {
		$t_issue_signature = HelpdeskPlugin::construct_mail_rollback_issue_signature( 1 );
		# Test # Issue tag is being set
		$this->assertNotFalse( 0, strpos( $t_issue_signature, "# Issue" ) );

		$t_ticket_position = strpos( $t_issue_signature, '# Hash' );
		# Test # Hash tag is being set
		$this->assertNotFalse( 0, $t_ticket_position );

		# Cut for inspection only this part of the string which contains '# Hash'
		$t_partial = substr( $t_issue_signature, $t_ticket_position );
		# Validate there is # Hash string being set
		$this->assertNotRegExp( '/\#\s?Hash:\s?([^\s]+)/im', $t_partial );
	}

	/**
	 * A test case that tests the following:
	 * 1. Remove # Issue, # Hash from provided string - in most cases MailGun body-plain parameter
	 * @return void
	 */
	public function testIssueNote() {
		$t_body_plain = helpdesk_description_from_reply_body( $this->prepareBodyPlainForTest() );
		$this->assertFalse( strpos( $t_body_plain, "---\n# Issue" ) );
	}

	/**
	 * A test case that tests the following:
	 * 1. Test retrieving email from name-email format, using `helpdesk_get_email_from_name_email`
	 * 2. Filter retrieved list of emails leaving only the unique records
	 * @return void
	 */
	public function testCleanupDuplicateRecipients() {
		$t_recipients = $this->prepareDuplicatedRecipients();

		$t_helpdesk = new HelpdeskPlugin();
		# Retrieve emails from name-email format and apply uniqueness filter
		$t_filtered_recipients = $t_helpdesk->cleanup_duplicate_recipients( $t_recipients );

		# There should be 8 emails left
		$this->assertEquals( 8, count($t_filtered_recipients) );
	}

	/**
	 * A test case that tests the following:
	 * 1. Retrieve recipients from MailGun headers To and Cc
	 * 2. Filter retrieved lists, removing `mantishub.(io.com)` domain emails
	 * 3. Filter out unique emails
	 * @return void
	 */
	public function testCollectingAdditionalRecipientsFromToCc() {
		$this->prepareGpcAdditionalRecipients();

		$f_additional_recipients_headers = gpc_get_string( 'To' ) . ',' . gpc_get_string( 'Cc' );
		$t_filtered_list = mantishub_collect_additional_recipients( $f_additional_recipients_headers );

		# Validate retrieved recipients list is not empty
		$this->assertEmpty( $t_filtered_list );

		# Filter collected list of recipients leaving only mantishub.(io|com) domain emails
		$t_filtered_list_with_mantishub_emails = array_filter($t_filtered_list, function($t_email) {
			return preg_match( '#[^\@]+@mantishub.(io|com)#', $t_email );
		});

		# Validate there are no emails with mantishub.(io|com) domain
		$this->assertEmpty( $t_filtered_list_with_mantishub_emails );

		# Validate only unique emails have been left
		$this->assertEquals( count($t_filtered_list), count( array_unique( $t_filtered_list ) ) );
	}

	/**
	 * Mailgun `body-plain` with # Issue and # Hash included, Host being retrieved dynamically by
	 * `mantishub_instance_name()`
	 *
	 * @return string
	 */
	private function prepareBodyPlainForTest() {
		return "administrator responded with the following:  Lorem ipsum dolor sit amet, tellus et eros erat sit dignissim erat, massa non eget porta curabitur aliquam, in ultricies sapien etiam vivamus. Est dolor vivamus, lectus nunc, consectetuer suscipit consectetuer sit ante eget tristique. Turpis ut in justo scelerisque nibh, morbi auctor nec. Nam id mauris, vitae mus amet dignissim auctor. Justo nec felis per. Taciti purus arcu, tincidunt gravida non etiam dis condimentum at, volutpat nec placerat praesent sagittis per sed, posuere quisque laoreet at, congue egestas.\n--- # Issue: 0000002 # Hash: " . mantishub_instance_name() . "+2-712b93d369f539b1fa06306162d80bee\nThis email is a service from MantisHub.  Reply to add a comment to issue. To learn more about using MantisHub, see our support portal at http://support.mantishub.com\n\n  --  Svetoslav Dragoev
		 :: Senior PHP Developer :: TL @ The Mags";
	}

	/**
	 * MailGun `recipient` header in different formats
	 * @return array
	 */
	private function prepareRecipients() {
		$t_instance_name = mantishub_instance_name();

		return array(
			$t_instance_name . '+4-061e6a47c97b2738124f06860a037b7e@mantishub.com',
			$t_instance_name . '+061e6a47c97b2738124f06860a037b7e@mantishub.com',
			$t_instance_name . '+helpdesk@mantishub.io',
		);
	}

	/**
	 * List of duplicated recipients in different formats
	 * @return array
	 */
	private function prepareDuplicatedRecipients() {
		return arrray(
			'test-1@mantishub.io',
			'test-2@mantishub.com',
			'test-3@mantishub.com',
			'test-3@mantishub.com',
			'test-4@mantishub.io',
			'test-5@mantishub.io',
			'Mantis Test<test-5@mantishub.io>',
			'Mantis Test <test-5@mantishub.io>',
			'Test Mantis <test-5@mantishub.io>',
			'Mantistest Mail <test-6@mantishub.com>',
			'Outside User <test-7@outside-user.io>',
			'Test-8@outside-user.uk'
		);
	}

	/**
	 * Set additional To, Cc recipients for testing `mantishub_collect_additional_recipients()` which collects that
	 * data from MailGun headers To and Cc, provided through GPS - _GET or _POST
	 * @return void
	 */
	private function prepareGpcAdditionalRecipients() {
		// set gpc
		$_GET['To'] = 'Mantis Test<test-1@mantishub.io>, Test Mantis <test-2@mantishub.com>, Outside User <Test-3@outside-user.io>, test-4@outside-user.uk';
		$_GET['Cc'] = 'Test-1@outside-cc.com, test-1@outside-cc.com, Mantis<Test-2@mantishub.com>, test-3<Test-3@test-me.com>, Test-4@outside-user.uk';
	}
}