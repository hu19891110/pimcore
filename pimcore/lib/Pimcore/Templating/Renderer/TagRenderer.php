<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Templating\Renderer;

use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\PageSnippet;
use Pimcore\Model\Document\Tag;
use Pimcore\Model\Document\Tag\Loader\TagLoaderInterface;
use Pimcore\Service\Request\EditmodeResolver;
use Pimcore\Templating\Model\ViewModel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TagRenderer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var TagLoaderInterface
     */
    protected $tagLoader;

    /**
     * @var EditmodeResolver
     */
    protected $editmodeResolver;

    /**
     * @param TagLoaderInterface $tagLoader
     * @param EditmodeResolver $editmodeResolver
     */
    public function __construct(TagLoaderInterface $tagLoader, EditmodeResolver $editmodeResolver)
    {
        $this->tagLoader        = $tagLoader;
        $this->editmodeResolver = $editmodeResolver;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function tagExists($type)
    {
        return $this->tagLoader->supports($type);
    }

    /**
     * Loads a document tag
     *
     * @param PageSnippet $document
     * @param $type
     * @param $inputName
     * @param array $options
     * @param bool|null $editmode
     *
     * @return Tag|null
     */
    public function getTag(PageSnippet $document, $type, $inputName, array $options = [], bool $editmode = null)
    {
        $type = strtolower($type);
        $name = Tag::buildTagName($type, $inputName, $document);

        //personal element name needed for real name of tag in case of personalized content - in order to build hierarchical name correctly
        $personalElementName = $inputName;
        if ($document && $document instanceof Page) {
            $personalElementName = $document->getPersonaElementName($inputName);
        }

        if (null === $editmode) {
            $editmode = $this->editmodeResolver->isEditmode();
        }

        try {
            $tag = null;

            if ($document instanceof PageSnippet) {
                $view = new ViewModel([
                    'editmode' => $editmode,
                    'document' => $document
                ]);

                $tag = $document->getElement($name);
                if ($tag instanceof Tag && $tag->getType() === $type) {
                    // call the load() method if it exists to reinitialize the data (eg. from serializing, ...)
                    if (method_exists($tag, 'load')) {
                        $tag->load();
                    }

                    $tag->setView($view);
                    $tag->setEditmode($editmode);
                    $tag->setOptions($options);
                } else {
                    $tag = Tag::factory($type, $name, $document->getId(), $options, null, $view, $editmode);
                    $document->setElement($name, $tag);
                }

                // set the real name of this editable, without the prefixes and suffixes from blocks and areablocks
                $tag->setRealName($personalElementName);
            }

            return $tag;
        } catch (\Exception $e) {
            $this->logger->warning($e);
        }
    }

    /**
     * Renders a tag
     *
     * @param PageSnippet $document
     * @param $type
     * @param $inputName
     * @param array $options
     * @param bool|null $editmode
     *
     * @return Tag|string
     */
    public function render(PageSnippet $document, $type, $inputName, array $options = [], bool $editmode = null)
    {
        $tag = $this->getTag($document, $type, $inputName, $options, $editmode);

        if ($tag) {
            return $tag;
        }

        return '';
    }
}
