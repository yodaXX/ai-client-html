<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2014-2022
 */

/* Available data:
 * - mediaItems : Media items incl. referenced items
 */

$enc = $this->encoder();


?>
<div class="catalog-detail-image">

	<?php if( ( $num = count( $this->get( 'mediaItems', [] ) ) ) > 1 ) : $class = 'item selected' ?>
		<div class="image-thumbs thumbs-vertical" data-slick='{"slidesToShow": 4, "slidesToScroll": 4, "vertical": true, "verticalSwiping": true}'><!--
			<?php if( $num > 4 ) : ?>
				--><button type="button" class="slick-prev"><?= $enc->html( $this->translate( 'client', 'Previous' ) ) ?></button><!--
			<?php endif ?>
			--><div class="thumbs"><!--

				<?php foreach( $this->get( 'mediaItems', [] ) as $id => $mediaItem ) : ?>

					--><a class="<?= $class ?>" title="<?= $enc->attr( $this->translate( 'client', 'Product image' ) ) ?>"
						style="background-image: url('<?= $enc->attr( $this->content( $mediaItem->getPreview(), $mediaItem->getFileSystem() ) ) ?>')"
						href="<?= $enc->attr( $this->link( 'client/html/catalog/detail/url', $this->get( 'params', [] ) ) ) . '#image-' . $enc->attr( $id ) ?>"
					></a><!--

					<?php $class = 'item' ?>
				<?php endforeach ?>

			--></div><!--
			<?php if( $num > 4 ) : ?>
				--><button type="button" class="slick-next"><?= $enc->html( $this->translate( 'client', 'Next' ) ) ?></button><!--
			<?php endif ?>
		--></div>
	<?php endif ?><!--

	--><div class="image-single" data-pswp="{bgOpacity: 0.75, shareButtons: false}">

		<?php foreach( $this->get( 'mediaItems', [] ) as $id => $mediaItem ) : ?>
			<div class="media-item">
				<?= $this->image( $mediaItem ) ?>
			</div>
		<?php endforeach ?>

	</div>


	<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="pswp__bg"></div>
		<div class="pswp__scroll-wrap">

			<!-- Container that holds slides. Don't modify these 3 pswp__item elements, data is added later on. -->
			<div class="pswp__container">
				<div class="pswp__item"></div>
				<div class="pswp__item"></div>
				<div class="pswp__item"></div>
			</div>

			<!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
			<div class="pswp__ui pswp__ui--hidden">
				<div class="pswp__top-bar">

					<div class="pswp__counter"></div>

					<button class="pswp__button pswp__button--close"
						title="<?= $enc->attr( $this->translate( 'client', 'Close' ) ) ?>">
					</button>
					<!-- button class="pswp__button pswp__button--share"
						title="<?= $enc->attr( $this->translate( 'client', 'Share' ) ) ?>">
					</button -->
					<button class="pswp__button pswp__button--fs"
						title="<?= $enc->attr( $this->translate( 'client', 'Toggle fullscreen' ) ) ?>">
					</button>
					<button class="pswp__button pswp__button--zoom"
						title="<?= $enc->attr( $this->translate( 'client', 'Zoom in/out' ) ) ?>">
					</button>

					<div class="pswp__preloader">
						<div class="pswp__preloader__icn">
							<div class="pswp__preloader__cut">
							<div class="pswp__preloader__donut"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
					<div class="pswp__share-tooltip"></div>
				</div>

				<button class="pswp__button pswp__button--arrow--left"
					title="<?= $enc->attr( $this->translate( 'client', 'Previous' ) ) ?>">
				</button>
				<button class="pswp__button pswp__button--arrow--right"
					title="<?= $enc->attr( $this->translate( 'client', 'Next' ) ) ?>">
				</button>

				<div class="pswp__caption"><div class="pswp__caption__center"></div></div>

			</div>
		</div>
	</div>

</div>
