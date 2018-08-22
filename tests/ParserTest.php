<?php

namespace Youthweb\SmileyEmojiMigration\Tests;

use Youthweb\SmileyEmojiMigration\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function testParseReadme()
	{
		$parser = new Parser;

		$entries = $parser->parseReadme();

		$this->assertTrue(is_array($entries));
		$this->assertCount(363, $entries);
	}

	/**
	 * @dataProvider EmojiUrlsProvider
	 */
	public function testParseEmojiUrls($string, $expected)
	{
		$parser = new Parser;

		$this->assertSame($expected, $parser->parseEmojiUrls($string));
	}

	/**
	 * EmojiUrlsProvider
	 */
	public function EmojiUrlsProvider()
	{
		return [
			['', []],
			[':question:', []],
			[
				'![](/dist/png-64/1f60a.png)',
				['/dist/png-64/1f60a.png']
			],
			[
				'![](/dist/png-64/1f60a.png)![](/dist/png-64/1f44d.png)',
				['/dist/png-64/1f60a.png', '/dist/png-64/1f44d.png']
			],
		];
	}

	/**
	 * @dataProvider EmojiCodesProvider
	 */
	public function testParseEmojiCodes($string, $expected)
	{
		$parser = new Parser;

		$this->assertSame($expected, $parser->parseEmojiCodes($string));
	}

	/**
	 * EmojiCodesProvider
	 */
	public function EmojiCodesProvider()
	{
		return [
			['', []],
			['``', []],
			[' `` ', []],
			['`:angry:`', [':angry:']],
			[' `:angry:` ', [':angry:']],
			[
				'`:worried::thumbsdown:`',
				[':worried:', ':thumbsdown:'],
			],
			[
				'`:computer::angry::punch:`',
				[':computer:', ':angry:', ':punch:'],
			],
		];
	}

	/**
	 * @dataProvider EmojiFilenamesProvider
	 */
	public function testParseEmojiFilenames($urls, $expected)
	{
		$parser = new Parser;

		$this->assertSame($expected, $parser->parseEmojiFilenames($urls));
	}

	/**
	 * EmojiFilenamesProvider
	 */
	public function EmojiFilenamesProvider()
	{
		return [
			[[], []],
			[
				['/dist/png-64/1f1e9-1f1ea.png'],
				['1f1e9-1f1ea.png'],
			],
			[
				['/dist/png-64/1f303.png', '/dist/png-64/1f3b7.png'],
				['1f303.png', '1f3b7.png'],
			],
		];
	}

	/**
	 * @dataProvider EntryProvider
	 */
	public function testEntryAttributes($entry)
	{
		$this->assertTrue(is_array($entry));
		$this->assertSame(
			[
				'smiley_code',
				'smiley_url',
				'smiley_filename',
				'emoji_codes',
				'emoji_urls',
				'emoji_filenames',
				'unicodes',
			],
			array_keys($entry)
		);

		// Prüfe die Urls
		$this->assertTrue(is_array($entry['emoji_urls']));

		if ( ! empty($entry['emoji_urls']) )
		{
			$url_regex = '~^\/dist\/png\-64\/[a-z0-9-]{4,}\.png$~is';

			foreach ($entry['emoji_urls'] as $url)
			{
				$this->assertRegExp($url_regex, $url);
			}
		}

		$this->assertTrue(is_array($entry['emoji_codes']));

		if ( ! empty($entry['emoji_codes']) )
		{
			$code_regex = '~:[a-z0-9_]+:~is';

			foreach ($entry['emoji_codes'] as $code)
			{
				$this->assertRegExp($code_regex, $code);
			}
		}

		$this->assertTrue(is_array($entry['emoji_filenames']));

		if ( ! empty($entry['emoji_filenames']) )
		{
			$filename_regex = '~[a-z0-9-]{4,}\.png~s';

			foreach ($entry['emoji_filenames'] as $filename)
			{
				$this->assertRegExp($filename_regex, $filename);
			}
		}

		// Prüfe, dass die Anzahl der Urls, Codes und Filenames gleich ist
		$expected_count = count($entry['emoji_urls']);

		$this->assertCount($expected_count, $entry['emoji_codes']);
		$this->assertCount($expected_count, $entry['emoji_filenames']);

		// return 'has_emoji' für die Statistik
		if ( ! empty($entry['emoji_urls']) )
		{
			return 'has_emoji';
		}
	}

	/**
	 * EntryProvider
	 */
	public function EntryProvider()
	{
		$parser = new Parser;

		$return = [];

		$entries = $parser->parseReadme();

		foreach ($entries as $entry)
		{
			$return[] = [$entry];
		}

		return $return;
	}
}
