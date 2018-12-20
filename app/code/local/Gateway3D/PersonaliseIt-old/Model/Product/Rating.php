<?php

/**
 * @category Gateway3D
 * @package Personalise-iT
 * @author James Ball <james@gateway3d.co.uk>
 * @copyright Copyright (c) 2013 Gateway 3D Ltd.
 */
class Gateway3D_PersonaliseIt_Model_Product_Rating
	extends Gateway3D_PersonaliseIt_Model_Product_Abstract
{
	public function setRating($rating = 1)
	{
		// Don't allow a rating < 1 or > 5
		$rating = max(1, min(5, $rating));
		
		// Limitation:
		//
		//		Currently we can only use the first rating that we find because
		//		there is nothing to tell us which rating to use!
		$ratingCollection = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->load()
            ->addOptionToItems();
		
		$ratingId = null;
		
		foreach($ratingCollection as $_rating)
		{
			$ratingId = $_rating->getId();
			break;
		}
		
		if($ratingId === null)
		{
			throw new Exception('No rating found');
		}
		
		// Now create the review
		$review = Mage::getModel('review/review')
					->setEntityPkValue($this->_product->getId())
					->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
					->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
					->setTitle("")
					->setDetail("")
					->setEntityId(1)
					->setStoreId(Mage::app()->getStore()->getId())
					->setStores(array(Mage::app()->getStore()->getId()))		
					->setNickname("")
					->save();
			
		// Add the rating to the review
		Mage::getModel('rating/rating')
			->setRatingId($ratingId)
			->setReviewId($review->getId())
			->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
			->addOptionVote(floor($rating), $this->_product->getId());
		
		// ?? Recalculate the review's rating summary?
		$review->aggregate();
		
		//
		return true;
	}
}