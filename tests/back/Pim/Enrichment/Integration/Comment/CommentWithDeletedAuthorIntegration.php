<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Comment;

use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;
use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentSubjectInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentWithDeletedAuthorIntegration extends TestCase
{
    /**
     * @test
     */
    public function a_user_can_be_deleted_if_he_commented_on_a_product()
    {
        $mary = $this->getUserByUsername('mary');
        $product = $this->createProduct('product_with_comment');
        $comment = $this->commentOnProduct($product, $mary, 'test');

        $this->get('pim_user.remover.user')->remove($mary);

        Assert::assertNull($this->getUserByUsername('mary'));

        $this->get('doctrine.orm.entity_manager')->refresh($comment);
        Assert::assertNull($comment->getAuthor());
    }

    /**
     * @test
     */
    public function a_user_can_be_deleted_if_he_replied_to_a_comment()
    {
        $product = $this->createProduct('product_with_comment');
        $comment = $this->commentOnProduct($product, $this->getUserByUsername('julia'), 'comment');

        $mary = $this->getUserByUsername('mary');
        $reply = $this->get('pim_comment.builder.comment')->buildReply($comment, $mary);
        $reply->setBody('reply to julia\'s comment');
        $this->get('pim_comment.saver.comment')->save($reply);

        $this->get('pim_user.remover.user')->remove($mary);

        Assert::assertNull($this->getUserByUsername('mary'));

        $this->get('doctrine.orm.entity_manager')->refresh($reply);
        Assert::assertNull($reply->getAuthor());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(string $identifier): ProductInterface
    {
        $product = $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, null);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function getUserByUsername(string $username): ?UserInterface
    {
        return $this->get('pim_user.repository.user')->findOneBy(['username' => $username]);
    }

    private function commentOnProduct(
        CommentSubjectInterface $product,
        UserInterface $user,
        string $body
    ): CommentInterface {
        $comment = $this->get('pim_comment.builder.comment')->buildComment($product, $user);
        $comment->setBody($body);
        $this->get('pim_comment.saver.comment')->save($comment);

        return $comment;
    }
}
