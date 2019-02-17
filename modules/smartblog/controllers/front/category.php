<?php

include_once(dirname(__FILE__).'/../../classes/controllers/FrontController.php');
if (!class_exists( 'DorImageBase' )) {     
    require_once (_PS_ROOT_DIR_.'/override/Dor/DorImageBase.php');
}
class smartblogCategoryModuleFrontController extends smartblogModuleFrontController
{
    public $phpself = 'dorblogs';
    public $ssl = true;
    public $smartblogCategory;

    public function init(){
            parent::init();
    }
    public function initContent(){
           parent::initContent();
           $dataItems = array();
           $category_status = '';
           $totalpages = '';
           $cat_image = 'no';
           $categoryinfo = '';
           $title_category = '';
           $cat_link_rewrite = '';
            $blogcomment = new Blogcomment();
            $SmartBlogPost = new SmartBlogPost();
            $BlogCategory = new BlogCategory();
            $BlogPostCategory = new BlogPostCategory();
            $thumbWidth = Configuration::get('blogThumbListWidth');
            $thumbHeight = Configuration::get('blogThumbListHeight');
            $dorBlogsStyle  = Tools::getValue('dorBlogsStyle',Configuration::get('dorBlogsStyle'));
            if(isset($dorBlogsStyle) && ($dorBlogsStyle == 3 || $dorBlogsStyle == 4 || $dorBlogsStyle == 5)){
                $thumbWidth = 510;
                $thumbHeight = 620;
            }else{
                $thumbWidth = $thumbWidth != ""?$thumbWidth:875;
                $thumbHeight = $thumbHeight != ""?$thumbHeight:500;
            }
            $thumbMainWidth = $thumbWidth;
            $thumbMainHeight = $thumbHeight;
            $sizeThumb = $thumbWidth."x".$thumbHeight;
            $thumbWidth2 = $thumbWidth;
            $thumbHeight2 = $thumbHeight;
            $id_category = Tools::getvalue('id_category');
                $posts_per_page = Configuration::get('smartpostperpage');
                $limitShortDesc = Configuration::get('limitShortDesc');
                
                if(isset($dorBlogsStyle) && ($dorBlogsStyle == 3 || $dorBlogsStyle == 4 || $dorBlogsStyle == 5)){
                    if($limitShortDesc > 200) $limitShortDesc = 150;
                    if($posts_per_page < 9) $posts_per_page = 9;
                }elseif(isset($dorBlogsStyle) && $dorBlogsStyle == 2){
                    $thumbWidth2 = 370;
                    $thumbHeight2 = 230;
                }
                $limit_start = 0;
                $limit = $posts_per_page;
                if(!$id_category = Tools::getvalue('id_category'))
                {
                        $total = $SmartBlogPost->getToltal($this->context->language->id);
                }else{
                        $total = $SmartBlogPost->getToltalByCategory($this->context->language->id,$id_category);
                        Hook::exec('actionsbcat', array('id_category' => Tools::getvalue('id_category')));
                }
                if($total != '' || $total != 0)
                    $totalpages = ceil($total/$posts_per_page);
                if((boolean)Tools::getValue('page')){
                $c = Tools::getValue('page');
                    $limit_start = $posts_per_page * ($c - 1);
            }
                if(!$id_category = Tools::getvalue('id_category'))
                {
                    $allNews = $SmartBlogPost->getAllPost($this->context->language->id,$limit_start,$limit);
                }else{
                    if (file_exists(_PS_MODULE_DIR_.'smartblog/images/category/' . $id_category. '.jpg'))
                    {
                       $cat_image =   $id_category;
                    }
                    else
                    {
                       $cat_image = 'no';
                    }
                    $categoryinfo = $BlogCategory->getNameCategory($id_category);
                    $title_category = $categoryinfo[0]['meta_title'];
                    $category_status = $categoryinfo[0]['active'];
                    $cat_link_rewrite = $categoryinfo[0]['link_rewrite'];
                    if($category_status == 1){
                    $allNews = $BlogPostCategory->getToltalByCategory($this->context->language->id,$id_category,$limit_start,$limit);
                    }
                    elseif($category_status == 0)
                    {
                    $allNews = '';
                    }
                }
            $i = 0;
            if(!empty($allNews)){
                
                foreach($allNews as $key=>$item){
                    $pathImg = "smartblog/images/".$item['post_img'].".jpg";
                    if($key==0){
                        $thumbWidth = $thumbMainWidth;
                        $thumbHeight = $thumbMainHeight;
                        $item['thumb_image'] = DorImageBase::renderThumb($pathImg,$thumbWidth,$thumbHeight);
                        if(isset($dorBlogsStyle) && ($dorBlogsStyle == 3 || $dorBlogsStyle == 4 || $dorBlogsStyle == 5)){
                            $item['thumb_image'] = DorImageBase::renderThumbMasonry($pathImg,$thumbWidth,$thumbHeight);
                        }
                    }else{
                        $thumbWidth = $thumbWidth2;
                        $thumbHeight = $thumbHeight2;
                        $item['thumb_image'] = DorImageBase::renderThumb($pathImg,$thumbWidth,$thumbHeight);
                        if(isset($dorBlogsStyle) && ($dorBlogsStyle == 3 || $dorBlogsStyle == 4 || $dorBlogsStyle == 5)){
                            $item['thumb_image'] = DorImageBase::renderThumbMasonry($pathImg,$thumbWidth,$thumbHeight);
                        }
                    }
                    
                    $to[$i] = $blogcomment->getToltalComment($item['id_post']);
                    $dataItems[$i] = $item;
                   $i++;
                }
                $j = 0;
                foreach($to as $item){
                    if($item == ''){
                        $dataItems[$j]['totalcomment'] = 0;
                    }else{
                        $dataItems[$j]['totalcomment'] = $item;
                    }
                    $j++;
                }
            }

            $this->context->smarty->assign( array(
                                            'page_name'=>"dorSmartBlogs",
                                            'modules_dir'=>_PS_MODULE_DIR_,
                                            'dorBlogsStyleCss'=>'dorStyleBlog'.$dorBlogsStyle,
                                            'postcategory'=>$dataItems,
                                            'limitShortDesc'=>$limitShortDesc,
                                            'category_status'=>$category_status,
                                            'title_category'=>$title_category,
                                            'cat_link_rewrite'=>$cat_link_rewrite,
                                            'id_category'=>$id_category,
                                            'cat_image'=>$cat_image,
                                            'categoryinfo'=>$categoryinfo,
                                            'smartshowauthorstyle'=>Configuration::get('smartshowauthorstyle'),
                                            'smartshowauthor'=>Configuration::get('smartshowauthor'),
                                            'limit'=>isset($limit) ? $limit : 0,
                                            'limit_start'=>isset($limit_start) ? $limit_start : 0 ,
                                            'c'=>isset($c) ? $c : 1,
                                            'total'=>$total,
                                            'smartblogliststyle' => Configuration::get('smartblogliststyle'),
                                            'smartcustomcss' => Configuration::get('smartcustomcss'),
                                            'smartshownoimg' => Configuration::get('smartshownoimg'),
                                            'smartdisablecatimg' => Configuration::get('smartdisablecatimg'),
                                            'smartshowviewed' => Configuration::get('smartshowviewed'),
                                            'post_per_page'=>$posts_per_page,
                                            'pagenums' => $totalpages - 1,
                                            'totalpages' =>$totalpages
                                            ));
            
               
            $this->setTemplate('smartblog/postcategory.tpl');        
    }
 }