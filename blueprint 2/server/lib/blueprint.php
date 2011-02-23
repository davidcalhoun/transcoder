<?php
	/**
	 * blueprint.php - Blueprint 1.1 simple emitter with documentation
	 * 
	 * This file includes extensive documentation for the PHP 5
	 * version of blueprint.
	 * 
	 * @version 1.1
	 * @copyright Copyright 2009 Yahoo! Inc. All rights reserved.
	 * @package blueprint
	 * 
	 * This code is part of the Yahoo! Mobile Widget SDK, governed by
	 * the Yahoo! Mobile Widget Developer Terms of Use at:
	 * http://info.yahoo.com/legal/us/yahoo/mobilewidgetdvlpr/mobilewidgetdvlpr-2070.html
	 */

	class _Container
	{
		protected $class;
		protected $content;

		public function addContent( $content )
		{
			if ( !isEmpty( $content ) )
			{
				if ( !is_array( $content ) )
					$content = array( $content );
	
				foreach( $content as $item )
					$this->addOne( $item );
			}
		}

		public function setClass( $class )
		{
			$this->class = $class;
		}

		public function getClass() {
			return $this->class;
		}

		public function emit( $writer )
		{
			$writer->startElement( $this->getTagName() );
			
			if ( !isEmpty( $this->class ) )
				$writer->writeAttribute( "class", $this->class );

			$this->emitAttributes( $writer );
			$this->emitAdditional( $writer );
				
			$this->emitContent( $writer );			

			$writer->endElement();
		}

		protected function emitContent( $writer )
		{
			if ( !isEmpty( $this->content ) )
			{
				foreach( $this->content as $item )
				{
					if ( is_string( $item ) )
						$writer->text( $item );
					else
						$item->emit( $writer );
				}
			}
		}

			
		protected function emitAdditional( $writer )
		{

		}
		
		protected function emitAttributes( $writer ) {
			
		}

		protected function isAllowed( $content )
		{
			return true;
		}

		protected function getTagName()
		{
			return "UNKNOWN";
		}
		
		protected function convertToAllowed( $inputContent )
		{
			return $inputContent;
		}

		protected function addOne( $content )
		{
			if ( !isEmpty( $content ) )
			{
				if ( ! $this->isAllowed( $content ) )
					$content = $this->convertToAllowed( $content );
				
				if ( $this->isAllowed( $content ) )
				{
					$this->content[] = $content;
				}
				else
				{
					error_log( "Error: Illegal attempt to add "
						. get_class( $content ) . " to " . $this->getTagName(), 0);
				}
			}
		}
	}
	
	class _ModuleBase extends _Container
	{	
		protected $head;

		public function setHeader( $h )
		{
			
			$this->head = $h;
		}
		
		protected function convertToAllowed( $inputContent)
		{
			// if the user is trying to add inline content, but inline content isn't directly
			// allowed, wrap it in a block
			if (Blueprint_isInlineContent( $inputContent) )
				return new Blueprint_Block( $inputContent );
			else
				return $inputContent;
		}
		
		public function emit( $writer )
		{
			$writer->startElement( $this->getTagName() );
			$this->emitAttributes( $writer );
			
			$this->emitHeader( $writer );
			
			$this->emitAdditional( $writer );
			
			$this->emitContent( $writer );
		
			$writer->endElement();
		}
		
		protected function emitHeader( $writer )
		{
			if ( !isEmpty( $this->head ) )
				$this->head->emit( $writer );
		}
	}

	class Blueprint_Generic
	{
		/**
		 * Creates a generic XML tag.
		 * @example Blueprint_Generic( "my-tag" ) will create <my-tag>
		 * @param ($tagName, $attributes = null, $content = null)
		 */
    	protected $tagName;
    	protected $attributes = array();
    	protected $content;
    	protected $_isControl = true;
    	protected $_isInline = false;

    	public function __construct($tagName, $attributes = null, $content = null)
    	{
        	$this->tagName = $tagName;
        	$this->setAttributes($attributes);
        	$this->content = $content;

    	}	

    	public function setControlType($isControl, $isInline)
    	{
        	$this->_isControl = $isControl;
        	$this->_isInline = $isInline;
    	}

    	public function addAttribute($name, $value)
    	{
        	$this->attributes[$name] = $value;
    	}

    	public function setAttributes($attributes)
		{
        	if (!empty($attributes))
            	$this->attributes = $attributes;
    	}

    	public function setContent($content) {
        	$this->setContent($content);
    	}

    	public function emit($writer)
    	{
        	$writer->startElement($this->tagName);
        
        	if (is_array($this->attributes))
			{
            	foreach($this->attributes as $name => $value) {
                	$writer->writeAttribute($name, $value);
            	}
        	}
        
        	if (!empty($this->content))
			{
            	$writer->text($this->content);
        	}
        
        	$writer->endElement($this->tagName);    
    	}
    
    	public function isControl()
    	{
        	return $this->_isControl;   
    	}	    
    	
		public function isInlineContent()
    	{
        	return $this->_isInline;    
    	}
	}


	class Blueprint_Page extends _ModuleBase
	{
		/**
		 * Creates a <page> element
		 * @example
		 * Blueprint_Page() will create the following:
		 *  <page>
		 *   <content/>
		 *  </page>
		 * Blueprint_Page( "stuff" ) will create the following:
		 *  <page>
		 *   <page-header>
		 *    <masthead layout="simple">
		 *     <layout-items>
		 *      <block>stuff</block>
		 *     </layout-items>
		 *    </masthead>
		 *   </page-header>
		 *   <content/>
		 *  </page>
		 * @param ($header = null, $content = null)
		 */
		
		protected $models = array();
		protected $goto = array();
		protected $options = array();
	    protected $pageFooter;
	    protected $style; //for style attribute
	
		public function __construct( $header = null, $content = null )
		{
			if (!isEmpty( $content ))
			{
				if (is_string($content) )
					$content = new Blueprint_Block($content);
				
				$this->addContent($content);
			}	
			if (!isEmpty( $header ))
			{
				$this->setHeader($header);
			}
				
		}
		
		public function setHeader( $h )
		{
			if ( !($h instanceof Blueprint_PageHeader) ) {
				$h = new Blueprint_PageHeader( $h );
			}
			
			$this->head = $h;
		}
	    
		public function setFooter($pageFooter)
    	{
        	$this->pageFooter = $pageFooter;
    	}

    	/**
		 * Deprecated, use setPageStyle instead
		 */
		public function setStyle($style)
    	{
        	$this->style = $style;
    	}

		public function setPageStyle($pageStyle) {
			$this->style = $pageStyle;
		}

		public function addOption( $o, $oAction = null )
		{
			if ( ! ( $o instanceof Blueprint_Option ) )
				$o = new Blueprint_Option( $o, $oAction );
			
			$this->options[] = $o;
		}

		public function addModel( $s )
		{
			$this->models[] = $s;
		}

		public function addGoToOption( $opt )
		{
			$this->goto[] = $opt;
		}
		
	    protected function emitAttributes ( $writer ) {
    	    if (!isEmpty($this->style)) {
            	$writer->writeAttribute('style', $this->style);
        	}
    	}

	    protected function emitAdditional( $writer )
    	{
        	if (!empty($this->pageFooter))
        	{
            	$this->pageFooter->emit($writer);
        	}
    	}

		public function emit( $writer )
		{
			$writer->startElement( "page" );
			$this->emitAttributes( $writer );
						
			Blueprint_EmitGroup( "models", $this->models, $writer );

			$this->emitHeader( $writer );
			
			$writer->startElement( "content" );
			
			$this->emitContent( $writer );
			
			Blueprint_EmitGroup( "options", $this->options, $writer );
						
			Blueprint_EmitGroup( "goto", $this->goto, $writer );

			$writer->endElement(); // content
			
			$this->emitAdditional( $writer );
			
			$writer->endElement(); // page
		}
		
		protected function getTagName()
		{
			return "page";
		}

		protected function isAllowed( $content )
		{
			return( $content instanceof Blueprint_Block
				|| $content instanceof Blueprint_Module
				|| $content instanceof Blueprint_Section
				|| Blueprint_isControl( $content ) );
		}
	
	}

	class Blueprint_Snippet
	{
		/**
		 * Creates a <snippet>
		 * @example 
		 * @param ($summary, $extended, $settings)
		*/
		
		protected $models = array();
		protected $summary;
		protected $extended;
		protected $settings;
		
		public function __construct( $summary, $extended, $settings )
		{
			$this->setSummary($summary);
			$this->setExtended($extended);
			$this->setSettings($settings);
		}
		
		public function setSummary( $s )
		{
			$this->summary = $s;
		}
		
		public function setExtended( $e )
		{
			$this->extended = $e;
		}

		public function setSettings( $settings )
		{
			$this->settings = $settings;
		}
		
		public function addModel( $s )
		{
			$this->models[] = $s;
		}
		
		public function emit( $writer )
		{
			$writer->startElement( "snippet" );
			
			Blueprint_EmitGroup( "models", $this->models, $writer );

			if ( !isEmpty( $this->summary ) )
				$this->summary->emit( $writer );
				
			if ( !isEmpty( $this->extended ) )
				$this->extended->emit( $writer );

			if ( !isEmpty( $this->settings ) )
				$this->settings->emit( $writer );
			
			$writer->endElement();
		}
	}
	
	class Blueprint_Summary
	{
		protected $content;
		
		public function __construct( $content )
		{
			if (!isEmpty( $content ))
				$this->addContent($content);
		}
		
		public function addContent( $c )
		{
			$this->content[] = $c;
		}
		
		public function emit( $writer )
		{
			$writer->startElement( "summary" );
			
			if ( !isEmpty( $this->content ) )
			{
				foreach( $this->content as $item )
					$item->emit( $writer );
			}
			
			$writer->endElement();
		}
	}
	
	class Blueprint_Extended
	{
		protected $content;
			
		public function __construct( $content )
		{
			if (!isEmpty( $content ))
				$this->addContent($content);
		}
		public function addContent( $c )
		{
			$this->content[] = $c;
		}
		
		public function emit( $writer )
		{
			$writer->startElement( "extended" );

			if ( !isEmpty( $this->content ) )
			{
				foreach( $this->content as $item )
					$item->emit( $writer );
			}			
			
			$writer->endElement();
		}
	}

	class Blueprint_Settings extends _Container
	{
		protected function getTagName()
		{
			return "settings";
		}
		
			
		public function __construct( $content )
		{
			if (!isEmpty( $content ))
				$this->addContent($content);
		}
		
		protected function isAllowed( $content )
		{
			return Blueprint_isInnerContent( $content );
		}
	}

	class Blueprint_Model
	{
	    const METHOD_URLENCODED_POST = "urlencoded-post";
	    const METHOD_GET = "get";
		
		protected $id;
		protected $submission;
		protected $instanceData;
		protected $dataName = "data";
		protected $xmlns = "";
    	protected $setfocus;
	
		public function __construct( $id = "", $dataName="data", $xmlns = "" )
		{
			$this->id = $id;
			$this->dataName = $dataName;
			$this->xmlns = $xmlns;
		}
		
		public function setSubmissionInfo( $id, $resource, $method = "urlencoded-post" , $secure = null)
		{
			$this->submission = new Blueprint_Submission( $id, $resource, $method , $secure);
		}
		
		public function addInstanceData( $name, $value = null )
		{
			$this->instanceData[] = array( $name, $value );
		}
		
		public function setFocus($setfocus)
    	{
        	if (!$setfocus instanceof Blueprint_Setfocus)
        	{
            	$setfocus = new Blueprint_Setfocus(
                	$setfocus, Blueprint_Setfocus::EVENT_PAGEREADY);
        	}

        	$this->setfocus = $setfocus;
    	}

		public function emit( $writer )
		{
			$writer->startElement( "model" );
			if ( !isEmpty( $this->id ) )
				$writer->writeAttribute( "id", $this->id );

			if ( !isEmpty( $this->instanceData ) )
			{
				$writer->startElement( "instance" );
				
				$writer->startElement( $this->dataName );
				$writer->writeAttribute( "xmlns", $this->xmlns );
				
				foreach( $this->instanceData as $item )
				{
					$writer->startElement( $item[0] );
					if ($item[1] instanceof Blueprint_LocationElement )
						$item[1]->emitLocation($writer);
					elseif (!isEmpty($item[1]))
						$writer->text( $item[1] );
					$writer->endElement();
				}
				
				$writer->endElement(); // dataName
				$writer->endElement(); // instance
			}

			if ( !isEmpty( $this->submission ) )
				$this->submission->emit( $writer );

			$this->emitAdditional( $writer );
			
			$writer->endElement(); // model
		}

	    public function emitAdditional($writer)
	    {
    	    if (!empty($this->setfocus))
        	{
            	$this->setfocus->emit($writer);
        	}
    	}
	
	}

	class Blueprint_Submission
	{
		protected $id;
		protected $resource;
		protected $method;
		protected $secure;
		protected $itemizeSelect;

		public function __construct( $id, $resource, $method = "urlencoded-post", $secure=null )
		{
			$this->id = $id;
			$this->resource = $resource;
			$this->method = $method;
			$this->secure = $secure;
		}

		public function setItemizeSelect($itemizeSelect)
		{
			$this->itemizeSelect = $itemizeSelect;
		}
		
		public function emit( $writer )
		{
			$writer->startElement( "submission" );
			
			if ( !isEmpty( $this->id ) )
				$writer->writeAttribute( "id", $this->id );
			if ( !isEmpty( $this->resource ) )
				$writer->writeAttribute( "resource", $this->resource );
			if ( !isEmpty( $this->method ) )
				$writer->writeAttribute( "method", $this->method );
			if ( !isEmpty( $this->secure ) )
				$writer->writeAttribute( "secure", $this->secure ? "true" : "false" );
			
			if ( !isEmpty( $this->itemizeSelect ) )
				$writer->writeAttribute( "itemize-select", $this->itemizeSelect ? "true" : "false" );
			
				
			$writer->endElement();
		}
	}
	
	class Blueprint_Module extends _ModuleBase
	{
		protected $id;
		protected $appearance;
		
		const APPEARANCE_NORMAL = 'normal';
		const APPEARANCE_GROUP = 'group';
	    	
		public function __construct( $header = null, $content = null )
		{
			if (!isEmpty( $content ))
			{
				if (is_string($content) )
					$content = new Blueprint_Block($content);
				
				$this->addContent($content);
			}	
			
			$this->setHeader($header);
							
		}
		
		public function setID($id)
    	{
        	$this->id = $id;
    	}

		public function setHeader( $header )
		{
			if (!isEmpty( $header ))
			{
				if (is_string($header))
					$header = new Blueprint_Header($header);
				parent::setHeader($header);
			}	
		}
		
		public function setAppearance($appearance = Blueprint_Module::APPEARANCE_NORMAL) {
			$this->appearance = $appearance;
		}
		
		protected function emitAttributes( $writer ) {
        	if ( !isEmpty($this->id) )
            	$writer->writeAttribute( "id", $this->id );
        	
			if ( !isEmpty($this->class) )
            	$writer->writeAttribute( "class", $this->class );

			//TODO:SET THE DEFAULT VAULE TO NORMAL AND ALWAYS OUTPUT THIS ATTRIBUTE
			if ( !isEmpty($this->appearance) ) {
				$writer->writeAttribute( "appearance", $this->appearance );
			}
    	}
			
		protected function getTagName()
		{
			return "module";
		}
		
		protected function isAllowed( $content )
		{
			return( $content instanceof Blueprint_Block
				|| $content instanceof Blueprint_Module
				|| $content instanceof Blueprint_Group
				|| Blueprint_isControl( $content ) );
		}
	}

	class Blueprint_Group extends _Container
	{
		const APPEARANCE_FULL = 'full';
		const APPEARANCE_COMPACT = 'compact';
		const APPEARANCE_MINIMAL = 'minimal';

		protected $appearance;
		
		public function __construct( $content = null, $class = 'normal' )
		{
			$this->addContent($content);
			$this->class = $class;
		}

		public function setAppearance($appearance)
		{
			$this->appearance = $appearance;
		}

		protected function emitAttributes($writer)
		{
			if (!isEmpty($this->appearance))
				$writer->writeAttribute('appearance', $this->appearance);
		}
		
		protected function getTagName()
		{
			return 'group';
		}

		
		protected function isAllowed( $content )
		{
			return( $content instanceof Blueprint_Block
				|| $content instanceof Blueprint_Group
				|| Blueprint_isControl( $content ) );
		}
	}


	class _ActionableElementBase
	{
		protected $actions;
	
		public function __construct( $action )
		{
			$this->addAction($action);
		}
		
		protected function isRelativeWidgetURL( $url )
		{
			if (stripos($url, 'widget:') !== 0 && stripos($url, '://') === FALSE) {
				return true;
			} else {
				return false;
			}
		}
		
		public function addAction( $action )
		{
			if ( (!isEmpty( $action )) && (is_string( $action )))
			{
				if ($this->isRelativeWidgetURL( $action ))
					$action = new Blueprint_LoadPageAction( $action );
				else
					$action = new Blueprint_LoadAction( $action );
			}
				
			if (!isEmpty( $action ))
				$this->actions[] = $action;
		}
		
		public function setAction( $action )
		{
			$this->actions = array();
			$this->addAction($action);
		}
		
		protected function emitActions( $writer )
		{
			if ( !isEmpty( $this->actions ) )
			{
				foreach( $this->actions as $action )
				{
					$action->emit( $writer );
				}
			}
		}
	}

	class _LayoutElementBase extends _ActionableElementBase
	{
		protected $layoutType;
		protected $blocks;
		protected $image;
		protected $rightImage;
		protected $imageTriggers;
		
		public function addContent( $content )
		{
			if ( !isEmpty( $content ) )
			{
				if ( !is_array( $content ) )
					$content = array( $content );
	
				foreach( $content as $item )
					$this->addOne( $item );
			}
		}
		
		protected function addOne( $content )
		{
			if ( !isEmpty( $content ) )
			{
				if ( !($content instanceof Blueprint_Block) )
					$content = new Blueprint_Block($content);
				$this->addBlock($content);
			}
		}
		
		protected function makeImageElement( $i )
		{
			if ( is_string( $i ) )
				$i = new Blueprint_Image( $i, $this->getDefaultImageSize() );

			return $i;
		}
		
		public function addBlock( $b )
		{
			$this->blocks[] = $b;
		}
		public function setLeftImage( $i )
		{
			$this->image = $this->makeImageElement($i);
		}		
		public function setRightImage( $i )
		{
			$this->rightImage = $this->makeImageElement($i);
		}
		public function setImages( $image )
		{
			if ( !isEmpty( $image ) )
			{
				if ( !is_array( $image ) )
					$image = array( $image );
	
				foreach( $image as $i )
					$this->addImage( $i );
			}
		}	
		public function addImage( $data )
		{
			if ( isEmpty( $this->image ) )
				$this->setLeftImage( $data );
			elseif ( isEmpty( $this->rightImage ) )
				$this->setRightImage( $data );
			else
				error_log( "Error: Only two images allowed in a placard or header", 0 );
		}

        public function addImageTrigger($imageTrigger)
        {
			$this->imageTriggers[] = $imageTrigger;
        }

		public function setImageTriggers($imageTriggers)
		{
			$this->imageTriggers = $imageTriggers;
		}
		
		protected function getDefaultImageSize()
		{
			return "medium";
		}	
		
		public function setLayoutType( $layout )
		{
			$this->layoutType = $layout;
		}		
		
		protected function getTagName()
		{
			return "UNKNOWN";
		}
		
		public function emit( $writer )
		{
			$writer->startElement( $this->getTagName() );
			$this->emitAttributes($writer);	
							
			$writer->startElement( "layout-items" );
			if ( !isEmpty( $this->image ) )
				$this->image->emit( $writer );
			if ( !isEmpty( $this->rightImage ) )
				$this->rightImage->emit( $writer );
				
			if ( !isEmpty( $this->imageTriggers) )
			{
				foreach( $this->imageTriggers as $trigger)
				{
					$trigger->emit( $writer );
				}
			}

			if ( !isEmpty( $this->blocks ) )
			{
				foreach( $this->blocks as $block )
				{
					$block->emit( $writer );
				}
			}
			$writer->endElement();  // layout-items
			
			$this->emitAdditional( $writer );
			
			$this->emitActions( $writer );
			
			$writer->endElement();  // tag
		}
		
		protected function emitAdditional( $writer )
		{

		}

		protected function emitAttributes( $writer ) {
			$writer->writeAttribute( "layout", $this->layoutType );
		}
		
	}

	class Blueprint_Header extends _LayoutElementBase
	{
		protected $uiCommand;

		public function __construct( $content = null, $image = null, $layout = "simple" )
		{
			$this->layoutType = $layout;
				
			$this->setImages($image);	
				
			if ( !isEmpty($content) )
			{
				$this->addContent($content);
			}
		}
	    
		public function setUiCommand($uiCommand)
    	{
        	$this->uiCommand = $uiCommand;
    	}

    	protected function emitAdditional( $writer )
    	{
        	if (!empty($this->uiCommand))
            	$this->uiCommand->emit( $writer );
    	}
		
		protected function getTagName()
		{
			return "header";
		}
		
		protected function getDefaultImageSize()
		{
			return "small";
		}	
	}
	
	class Blueprint_Masthead extends _LayoutElementBase
	{		
		public function __construct( $content = null, $image = null, $layout = "simple" )
		{
			$this->layoutType = $layout;
				
			$this->setImages($image);	
				
			if ( !isEmpty($content) )
			{
				$this->addContent($content);
			}
		}
		
		protected function getTagName()
		{
			return "masthead";
		}
		
		protected function getDefaultImageSize()
		{
			return "small";
		}	
	}	
	
	class Blueprint_PageHeader extends _Container  
	{
		protected $titleBar;
		protected $pageTitle;
		protected $pageLogo;
		protected $backTitle;
		protected $appearance;
		
		public function __construct( $content = null )
		{
			$this->addContent($content);	
		}

	    public function setTitleBar($titleBar)
	    {
    	    $this->titleBar = $titleBar;
    	}

		public function setOnesearch($onesearch)
		{
        	$this->addContent($onesearch);
		}

		public function setLinkSet($linkSet)
		{
			$this->addContent($linkSet);
		}

		public function setSearchBox($searchBox)
		{
			$this->addContent($searchBox);
		}

		public function setPageTitle($pageTitle) {
			$this->pageTitle = $pageTitle;
			$this->pageLogo = null;
		}

		public function setPageLogo($pageLogo) {
			$this->pageLogo = $pageLogo;
			$this->pageTitle = null;
		}

		public function setBackTitle($backTitle) {
			$this->backTitle = $backTitle;
		}

		public function setAppearance( $appearance = 'full' )
		{
			$this->appearance = $appearance;
		}

		public function getAppearance() {
			return $this->appearance;
		}
		
		protected function isAllowed( $content )
		{
			return (!is_string($content));
		}

		protected function emitAttributes($writer)
		{
			if (!isEmpty($this->appearance))
				$writer->writeAttribute('appearance', $this->appearance);
		}

		protected function emitAdditional($writer)
		{
			if (!isEmpty($this->titleBar))
				$this->titleBar->emit($writer);

			if (!isEmpty($this->pageTitle))
				$writer->writeElement('page-title', $this->pageTitle);

			if (!isEmpty($this->pageLogo)) {
				$writer->startElement('page-logo');
				$writer->writeAttribute('resource', $this->pageLogo);
				$writer->endElement();
			}

			if (!isEmpty($this->backTitle))
				$writer->writeElement('back-title', $this->backTitle);
		}
		
		protected function getTagName()
		{
			return "page-header";
		}
		
		protected function convertToAllowed( $inputContent)
		{
			return new Blueprint_Masthead($inputContent);
		}
	}
	
	
	class Blueprint_UiCommand extends _ActionableElementBase
	{
		protected $title;
		protected $icon;

		public function __construct($title, $icon = null, $action = null)
		{
			$this->title = $title;
			$this->icon = $icon; 

			if (!isEmpty($action))
			{
				$this->addAction( $action );
			}			
		}

		public function setTitle($title) {
			$this->title = $title;
		}

		public function setIcon($icon) {
			$this->icon = $icon;
		}

		public function emit($writer) {
			$writer->startElement($this->getTagName());

			if (!isEmpty($this->title))
				$writer->writeElement("title", $this->title);

			if (!isEmpty($this->icon))
				$writer->writeElement("icon", $this->icon);

			$this->emitActions($writer);

			$writer->endElement();  // option
		}

		protected function getTagName()
		{
			return "ui-command";
		}

	}
	
	class Blueprint_Setfocus
	{
		const EVENT_PAGEREADY = "page-ready";
		
		protected $control;
		protected $event;

		public function __construct($control, $event = null) {
			$this->control = $control;
			$this->event = $event;
		}
	    
		public function setControl($control) {
			$this->control = $control;
		}

		public function setEvent($event) {
			$this->event = $event;
		}

		public function emit( $writer )
		{
			$writer->startElement($this->getTagName());

			$writer->writeAttribute("control", $this->control);
			if (!isEmpty($this->event))
				$writer->writeAttribute("event", $this->event);

			$writer->endElement();
		}

		protected function getTagName()
		{
			return "setfocus";
		}
	}
	
	
	class Blueprint_TitleBar {
		const SUPPRESS = "SUPPRESS";

		protected $logo = null;
		protected $title = null;
		protected $commands = array();


		public function __construct($title = null, $logo = null, $commands = null)
		{
			$this->title = $title;
			$this->logo = $logo;

			$this->addCommand($commands);
		}

		public function setLogo($logo = self::SUPPRESS)
		{
			$this->logo = $logo;
		}

		public function setTitle($title = self::SUPPRESS)
		{
			$this->title = $title;
		}

		public function suppressCommands()
		{
			$this->commands = self::SUPPRESS;
		}

		public function addCommand( $uiCommand )
		{
			if ($this->commands === self::SUPPRESS)
			$this->commands = array();

			if ( is_array( $uiCommand ) )
			{
				foreach( $uiCommand as $c )
				$this->addCommand( $c );
			}
			else
			{
				if ( !isEmpty( $uiCommand ) )
				{
					$this->commands[] = $uiCommand;
				}
			}
		}

		public function emit( $writer )
		{
			$writer->startElement( "title-bar" );

			if (isEmpty($this->logo) || $this->logo === self::SUPPRESS) {
				$writer->startElement( "logo" );
				$writer->endElement();
			} else {
				$writer->startElement( "logo" );
				$writer->writeAttribute( "resource", $this->logo);
				$writer->endElement();
			}
			
			if ($this->title === self::SUPPRESS) {
				$writer->startElement( "title" );
				$writer->endElement();
			} else if ($this->title !== null)
				$writer->writeElement( "title", $this->title );
			

			if ($this->commands === self::SUPPRESS) {
				$writer->startElement( "commands" );
				$writer->endElement();
			} elseif ($this->commands !== null)
				Blueprint_EmitGroup( "commands", $this->commands, $writer );

			$writer->endElement();  // title-bar
		}
	}
	
	class Blueprint_Category extends Blueprint_Block
	{
	    protected $selected = null;
	    protected $fixed = null;
		protected $visible = 'true';
	    protected $title = null;
	    protected $value = null;
	    protected $subvalue = null;

	    const CHECKED = 'checked';
	    const UNCHECKED = 'unchecked';

	    public function __construct($selected=null, $fixed=null,$title=null,$value=null,$subvalue=null)
	    {
	        $this->selected = $selected;
	        $this->fixed = $fixed;
	        $this->title = $title;
	        $this->value = $value;
	        $this->subvalue = $subvalue;
	    }

		public function setVisible($visible = 'true') {
			$this->visible = $visible;
		}

	    public function emit($writer) 
	    {
	        $writer->startElement('category');

	        if ( !is_null($this->selected) ) {
	            if(self::CHECKED == $this->selected) {
	                $writer->writeAttribute( "selected", 'true');
	            } else if(self::UNCHECKED == $this->selected) {
	                $writer->writeAttribute( "selected", 'false');
	            }
	        }

	        if ( !is_null($this->fixed)) {
	            if(self::CHECKED == $this->fixed) {
	                $writer->writeAttribute( "fixed", 'true');
	            } else if(self::UNCHECKED == $this->fixed) {
	                $writer->writeAttribute( "fixed", 'false');
	            }
	        }

			$writer->writeAttribute('visible', $this->visible);

	        if(!isEmpty($this->title)) {
	            $writer->startElement('title');
	            $writer->text($this->title);
	            $writer->endElement();
	        }
	        if(!isEmpty($this->value)) {
	            $writer->startElement('value');
	            $writer->text($this->value);
	            $writer->endElement();
	        }
	        if(!isEmpty($this->subvalue)) {
	            $writer->startElement('subvalue');
	            $writer->text($this->subvalue);
	            $writer->endElement();
	        }

	        $writer->endElement();
	    }
	}
	
	class Blueprint_Onesearch extends Blueprint_Block
	{
	    private $query = null;
	    private $location = null;
	    private $context = null;
	    private $category = null;
		private $logo = null;

		protected $showLocation = 'true';
		protected $setCurrent = 'false';
		protected $keyFields = array();

		const LOGO_DEFAULT = 'default';
		const LOGO_PARTNER = 'partner';
		const LOGO_NONE = 'none';


	    public function __construct($query = null, $context = null, $category = null)
	    {
	        $this->query = $query;
	        $this->context = $context;
	    }

		public function setShowLocation($showLocation = 'true') {
			$this->showLocation = $showLocation;
		}

		public function setSetCurrent($setCurrent = 'false') {
			$this->setCurrent = $setCurrent;
		}

	    public function setContext($context) 
	    {
	        $this->context = $context;
	    }

	    public function setCategory($cat) 
	    {
	        $this->category = $cat;
	    }

	    public function setQuery($query) 
	    {
	        $this->query = $query;
	    }

	    public function addLocation($location) 
	    {
	        $this->location = $location;
	    }

		public function setLogo($logo) {
			$this->logo = $logo;
		}

		public function addkeyField($name, $value) {
			$this->keyFields[$name] = $value;
		}

		public function setKeyFields($keyFields) {
			$this->keyFields = $keyFields;
		}

	    public function emit($writer) 
	    {
	        $writer->startElement('one-search');

			$writer->writeAttribute('show-location', $this->showLocation);
			$writer->writeAttribute('set-current', $this->setCurrent);
			
	        if (!isEmpty($this->logo)) {
				$writer->writeAttribute('logo', $this->logo);
			}
			
			if (!isEmpty($this->query)) {
	            $writer->startElement('query');
	            $writer->text($this->query);
	            $writer->endElement();
	        }
			
	        if (!isEmpty($this->location)) {
	            $this->location->emit($writer);
	        }
			
	        if (!isEmpty($this->context)) {
	            $writer->startElement('context');
	            $writer->text($this->context);
	            $writer->endElement();
	        }
			
	        if (!isEmpty($this->category)) {
	            $this->category->emit($writer);
	        }

			if (!isEmpty($this->keyFields)) {
				foreach ($this->keyFields as $name => $value) {
					$writer->startElement('keyfield');
					$writer->writeAttribute('name', $name);
					$writer->writeAttribute('value', $value);
					$writer->endElement();
				}
			}


	        $writer->endElement();
	    }
	}
	
	class Blueprint_PageFooter extends Blueprint_Module {
		protected function getTagName()
		{
			return "page-footer";
		}

		public function insertDefaultFooter()
		{
			$this->content[] = new Blueprint_Generic("default-footer");
		}

		public static function getSuppressedFooter()
		{
			return new Blueprint_Generic("page-footer");
		}	
	}
	
	class Blueprint_SearchBox
	{
	    protected $submission;
	    protected $label;
	    protected $ref;
	    protected $model;

	    public function __construct($submission, $ref = null, $label = null, $model = null)
	    {
	        $this->submission = $submission;
	        $this->ref = $ref;

	        if (!isEmpty($label) && !($label instanceof Blueprint_Label))
	            $label = new Blueprint_Label($label);

	        $this->label = $label;
	        $this->model = $model;
	    }


	    public function setLabel($label) 
	    {
	        if (!isEmpty($label) && !($label instanceof Blueprint_Label))
	            $label = new Blueprint_Label($label);

	        $this->label = $label;
	    }

	    public function setSubmission($submission) 
	    {
	        $this->submission = $submission;
	    }

	    public function setRef($ref) 
	    {
	        $this->ref = $ref;
	    }

	    public function setModel($model) 
	    {
	        $this->model = $model;
	    }

	    public function emit($writer) 
	    {
	        $writer->startElement($this->getTagName());

	        if (!isEmpty($this->submission))
	            $writer->writeAttribute('submission', $this->submission);

	        if (!isEmpty($this->ref))
	            $writer->writeAttribute('ref', $this->ref);

	        if (!isEmpty($this->model))
	            $writer->writeAttribute('model', $this->model);

	        if(!isEmpty($this->label)) {
	            $this->label->emit($writer);
	        }

	        $writer->endElement();
	    }

	    public function isControl()
	    {
	        return true;
	    }

	    protected function getTagName()
	    {
	        return 'search-box';
	    }
	}

	class Blueprint_TemplateItem
	{
		protected $field;
		protected $content;

		public function __construct($field, $content = null)
		{
			$this->field = $field;
			$this->content = $content;
		}

		public function setField($field)
		{
			$this->field = $field;
		}

		public function setContent($content)
		{
			$this->content = $content;
		}

		public function emit($writer)
		{
			$writer->startElement('template-item');
			$writer->writeAttribute('field', $this->field);

			if (!isEmpty($this->content))
			{
				$this->content->emit($writer);
			}

			$writer->endElement();	
		}
	}

	class Blueprint_TemplateItems
	{
		protected $format;
		protected $items = array();

		const FORMAT_TITLE_VALUE = 'title-value';

		public function __construct($format = self::FORMAT_TITLE_VALUE) {
			$this->format = $format;
		}

		public function setFormat($format) {
			$this->format = $format;
		}

		public function addItem($item) {
			$this->items[] = $item;
		}

		public function setItems($items) {
			$this->items = $items;
		}

		public function emit($writer) {
			$writer->startElement('template-items');
			$writer->writeAttribute('format', $this->format);

			if (!isEmpty($this->items)) {
				foreach($this->items as $item) {
					$item->emit($writer);
				}
			}

			$writer->endElement();
		}
	}

	class Blueprint_Placard extends _LayoutElementBase
	{
		protected $class;
		protected $templateItems;
		protected $id;
		
		public function __construct( $type = null, $content = null, $image = null, $isLink = false )
		{
			if ( isEmpty( $type ) )	
				$this->layoutType = "simple";
			else
				$this->layoutType = $type;
				
			$this->setImages( $image );
				
			if ( !isEmpty($content) )
			{
				$this->addContent($content);
			}
			
			$this->setShowLink($isLink);
		}
			
		public function setShowLink( $showIt = true )
		{
			if ($showIt)
				$this->class = "link";
			else
				$this->class = "";
		}		

	    public function setClass( $class )
    	{
        	$this->class = $class;
		}

		public function setId( $id ) {
			$this->id = $id;
		}

		public function setTemplateItems($templateItems)
		{
			$this->templateItems = $templateItems;
		}

		public function emit( $writer )
		{
			if (strcmp($this->layoutType, 'template') != 0) {
				parent::emit($writer);
			} else {
				$writer->startElement( $this->getTagName() );
				$this->emitAttributes( $writer );
							
				if (!isEmpty($this->templateItems))
					$this->templateItems->emit($writer);
				
				$this->emitAdditional($writer);
				$this->emitActions( $writer );
			
				$writer->endElement(); 
			}
		}

		
		protected function getTagName()
		{
			return "placard";
		}
		
		protected function emitAttributes( $writer )
		{
			if ( !isEmpty($this->id) )
				$writer->writeAttribute( "id", $this->id );	
			
			if ( !isEmpty($this->class) )
				$writer->writeAttribute( "class", $this->class );	
			

			//Output layout	
			parent::emitAttributes($writer);
		}

	}
	
	class Blueprint_Block extends _Container
	{		
	    const HALIGN_NATURAL = "natural";
    	const HALIGN_OPPOSITE = "opposite";
    	const HALIGN_RIGHT = "right";
    	const HALIGN_CENTER = "center";
    	const HALIGN_LEFT = "left";
    
		protected $lines;
    	protected $halign;
    
    	public function setHalign($halign = Blueprint_Block::HALIGN_NATURAL)
    	{
        	$this->halign = $halign;
    	}
    
		function __construct( $content = "", $class = null, $lines = null )
		{
			$this->class = $class;
			$this->addContent( $content );
			$this->lines = $lines;
		}
		
		public function setClass( $class )
		{
			$this->class = $class;
		}
		
		public function setLines( $numLines )
		{
			$this->lines = $numLines;
		}

		protected function isAllowed( $content )
		{
			return Blueprint_isInlineContent( $content );
		}
		
		protected function convertToAllowed( $inputContent)
		{
			// can't wrap disallowed objects to make them allowed
			return $content;
		}
		
		protected function getTagName()
		{
			return "block";
		}
		
		protected function emitAttributes( $writer )
		{
			if ( !isEmpty( $this->lines ) )
				$writer->writeAttribute( "lines", $this->lines );

        	if (! isEmpty( $this->halign))
            	$writer->writeAttribute("halign", $this->halign);
		}
	}

	class Blueprint_Span extends _Container
	{
		public function __construct( $content, $class = null )
		{
			$this->class = $class;
			$this->addContent( $content );
		}

		protected function isAllowed( $content )
		{
			return( is_string( $content )
				|| $content instanceof Blueprint_Em
				|| $content instanceof Blueprint_Strong );
		}
		
		protected function convertToAllowed( $inputContent)
		{
			// can't wrap disallowed objects to make them allowed
			return $content;
		}
		
		protected function getTagName()
		{
			return "span";
		}
	}

	class Blueprint_Strong extends _Container
	{
		function __construct( $content = "" )
		{
			$this->addContent( $content );
		}
		
		public function isAllowed( $content )
		{
			return( is_string( $content ) );
		}
		
		protected function getTagName()
		{
			return "strong";
		}
	}

	class Blueprint_Em extends _Container
	{
		public function __construct( $content = "" )
		{
			$this->addContent( $content );
		}
		
		protected function isAllowed( $content )
		{
			return( is_string( $content ) );
		}
		
		protected function getTagName()
		{
			return "em";
		}
	}
	
	class Blueprint_Br
	{
		public function emit( $writer )
		{
			$writer->startElement( "br" );
			$writer->endElement();
		}
	}
		
	class Blueprint_Image
	{
		protected $resource;
		protected $size;
		protected $caption;
		protected $fillStyle;
		protected $width;
		protected $height;
		protected $class;

		function __construct( $resource, $size = null, $style = null, $captionText = null )
		{
			$this->resource = $resource;
			$this->size = $size;
			$this->fillStyle = $style;
			$this->caption = $captionText;
		}

		public function setResource($resource) {
			$this->resource = $resource;
		}
			
		public function setCaption( $text )
		{
			$this->caption = $text;
		}		
		
		public function setFillStyle( $style )
		{
			$this->fillStyle = $style;
		}

		public function setWidth($width = 'medium')
		{
			$this->width = $width;
		}
		
		public function setHeight($height = 'medium')
		{
			$this->height = $height;
		}

		public function emit( $writer )
		{
			$writer->startElement( "image" );
			if (!isEmpty($this->size))
				$writer->writeAttribute( "size", $this->size );
				
			
			if (!isEmpty($this->height))
				$writer->writeAttribute( "height", $this->height );
				
			if (!isEmpty($this->width))
				$writer->writeAttribute( "width", $this->width );

			if ( !isEmpty( $this->caption ) )
				$writer->writeAttribute( "caption", $this->caption );
			
			if ( !isEmpty( $this->fillStyle ) )
				$writer->writeAttribute( "fill-style", $this->fillStyle );

			$writer->writeAttribute('resource', $this->resource);

			$writer->endElement();
		}
	}

	abstract class _PhotoBase
	{
		protected $resource;
		protected $fillStyle;
		protected $caption;

		public function __construct($resource, $fillStyle = null, $caption = null)
		{
			$this->resource = $resource;
			$this->fillStyle = $fillStyle;
			$this->caption = $caption;
		}

		public function setFillStyle($fillStyle)
		{
			$this->fillStyle = $fillStyle;
		}

		public function setCaption($caption) {
			$this->caption = $caption;
		}

		public function setResource($resource) {
			$this->resource = $resource;
		}
		
		public function emit($writer)
		{
			$writer->startElement($this->getTagName());

			if (!isEmpty($this->fillStyle))
				$writer->writeAttribute('fill-style', $this->fillStyle);

			if (!isEmpty($this->caption))
				$writer->writeAttribute('caption', $this->caption);

			$writer->writeAttribute('resource', $this->resource);

			if (!isEmpty($this->options)) {
				$this->options->emit($writer);
			}

			$writer->endElement();
		}
		
		public function isControl() {
			return true;
		}

		abstract protected function getTagName();
	}

	class Blueprint_Photo extends _PhotoBase {
		protected function getTagName() {
			return 'photo';
		}
	}

	class Blueprint_Banner extends _PhotoBase {
		protected function getTagName() {
			return 'banner';
		}
	}

	class Blueprint_Option extends _ActionableElementBase
	{
		protected $label;
		protected $image;
		
		public function __construct( $label, $action = null )
		{
			if (! ($label instanceof Blueprint_Label ) )
				$label = new Blueprint_Label( $label );
			
			$this->label = $label;
			
			if (!isEmpty($action))
			{
				$this->addAction( $action );
			}
		}

		public function setLabel($label) {
			if (!($label instanceof Blueprint_Label))
				$label = new Blueprint_Label($label);

			$this->label = $label;
		}

		public function setImage($image) {
			$this->image = $image;
		}

		public function emit( $writer )
		{
			$writer->startElement($this->getTagName());
			
			if ( !isEmpty( $this->label ) )
				$this->label->emit( $writer );
			
			if ( !isEmpty( $this->image ) )
				$this->image->emit( $writer );
			
			$this->emitActions( $writer );

			$writer->endElement();  // option
		}

		protected function getTagName() {
			return 'option';
		}
	}

	class Blueprint_SelectBase
	{
		protected $items;
		protected $label;
		protected $ref;
		protected $model;
		protected $tagName;
		protected $appearance;
			
		protected function __construct( $tagname, $label, $ref, $model, $items = null )
		{
			$this->tagName = $tagname;
			$this->ref = $ref;
			$this->model = $model;

			if (! ($label instanceof Blueprint_Label ) )
				$label = new Blueprint_Label( $label );
				
			$this->label = $label;
			
			if (! isEmpty($items))
			{
				foreach ($items as $label => $value) {
					$this->addItem( $label, $value);
				}
			}
		}
		
		public function addItem( $label, $value )
		{
			if (! ($label instanceof Blueprint_Label ) )
				$label = new Blueprint_Label( $label );
			$this->items[$value] = $label;
		}
		
		public function setAppearance($appearance) {
			$this->appearance = $appearance;
		}
		
		public function emit( $writer )
		{
			$writer->startElement( $this->tagName );
			
			if ( !isEmpty( $this->ref ) )
				$writer->writeAttribute( "ref", $this->ref );

			if ( !isEmpty( $this->model ) )
				$writer->writeAttribute( "model", $this->model );

			if ( !isEmpty( $this->appearance ) )
				$writer->writeAttribute( "appearance", $this->appearance );
			
			// write the label
			if ( !isEmpty( $this->label ) )
			{
				$this->label->emit($writer);
			}
			
			if ( !isEmpty( $this->items ) )
			{
				foreach( $this->items as $v => $l )
				{
					$writer->startElement( "item" );
						$l->emit($writer);
						$writer->startElement( "value" );
						$writer->text( $v );
						$writer->endElement();
					$writer->endElement();  // item
				}
			}

			$writer->endElement(); // select
		}
	}

	class Blueprint_Select1 extends Blueprint_SelectBase
	{
		const APPEARANCE_POPUP = 'popup';
		const APPEARANCE_COMPACT_POPUP = 'compact-popup';
		const APPEARANCE_RADIO_GROUP = 'radio-group';
		
		public function __construct(
			$label, $ref = "", $model = "",
			$appearance = self::APPEARANCE_POPUP, $items = null)
		{
			$this->appearance = $appearance;
			
			parent::__construct( "select1", $label, $ref, $model, $items );
			
		}

	}

	class Blueprint_Select extends Blueprint_SelectBase
	{
		const APPEARANCE_PLACARD = "placard";
		const APPEARANCE_CHECKBOXES = "checkboxes";
		const APPEARANCE_TOGGLE = "toggle";

		function __construct( $label, $ref = "", $model = "", $items = null )
		{
			parent::__construct( "select", $label, $ref, $model, $items );
		}

	    public function addPlacard( $placard, $value )
	    {
	        if (is_string($placard))
	            $placard = new Blueprint_Placard("simple", $placard);

	        $this->items[$value] = $placard;
	        $this->appearance = self::APPEARANCE_PLACARD;
	    }

	    public function addItem( $label, $value )
	    {
	        parent::addItem($label, $value);
	        $this->appearance = self::APPEARANCE_CHECKBOXES;
	    }
	}

	class Blueprint_Submit extends Blueprint_Trigger
	{
		protected function getTagName()
		{
			return "submit";
		}
	
		
		function __construct( $label, $model = "", $appearance = "button" )
		{
			if (! ($label instanceof Blueprint_Label ) )
				$label = new Blueprint_Label( $label );
			
			$this->label = $label;

			$this->model = $model;
			$this->appearance = $appearance;
		}
	}

	class Blueprint_Trigger extends _ActionableElementBase
	{
		protected $label;
		protected $appearance;
		protected $model;
				
		public function __construct( $label, $action = null, $appearance = "button" )
		{
			if (! ($label instanceof Blueprint_Label ) )
				$label = new Blueprint_Label( $label );
			
			$this->label = $label;
			
			$this->addAction($action);
			$this->appearance = $appearance;
		}

		public function setAppearance($appearance) {
			$this->appearance = $appearance;
		}
		
		protected function getTagName()
		{
			return "trigger";
		}
		
		public function emit( $writer )
		{
			$writer->startElement( $this->getTagName() );
			
			if ( !isEmpty( $this->appearance ) )
				$writer->writeAttribute( "appearance", $this->appearance );

			// write the model, if applicable
			if ( !isEmpty( $this->model ) )
				$writer->writeAttribute( "model", $this->model );
				
			// write the label
			if ( !isEmpty( $this->label ) )
				$this->label->emit( $writer );

			// write the action
			$this->emitActions( $writer );

			$writer->endElement();
		}
	}

	class Blueprint_Input
	{
		const KEYBOARD_NORMAL = 'normal';
		const KEYBOARD_EMAIL = 'email';
		const KEYBOARD_URL = 'url';
		const KEYBOARD_NUMBERS = 'numbers';
		const KEYBOARD_PHONES = 'phones';

		const APPEARANCE_FULL = 'full';
		const APPEARANCE_COMPRESSED = 'compressed';
		
		protected $label;
		protected $ref;
		protected $model;
		protected $keyboard;
		protected $placeholder;
		protected $appearance = self::APPEARANCE_FULL;

		public function __construct( $label, $ref = "", $model = "" )
		{
			if (! ($label instanceof Blueprint_Label ) )
				$label = new Blueprint_Label( $label );

			$this->label = $label;
			$this->ref = $ref;
			$this->model = $model;
		}

		public function setKeyboard($keyboard = self::KEYBOARD_NORMAL)
		{
			$this->keyboard = $keyboard;
		}

		public function setAppearance($appearance) {
			$this->appearance = $appearance;
		}
		
		public function setPlaceholder($placeholder)
		{
			$this->placeholder = $placeholder;
		}

		public function emit( $writer )
		{
			$writer->startElement( $this->GetTagName() );
			
			if ( !isEmpty( $this->ref ) )
				$writer->writeAttribute( "ref", $this->ref );

			if ( !isEmpty( $this->model ) )
				$writer->writeAttribute( "model", $this->model );

			if ( !isEmpty( $this->keyboard )) 
				$writer->writeAttribute( "keyboard", $this->keyboard );
			
			if ( !isEmpty( $this->appearance))
				$writer->writeAttribute( "appearance", $this->appearance );
			
			if ( !isEmpty( $this->label ) )
				$this->label->emit( $writer );

			if ( !isEmpty( $this->placeholder))
				$writer->writeElement( "placeholder", $this->placeholder );


			$writer->endElement();
		}
		
		protected function getTagName()
		{
			return "input";
		}
	}
	
	class Blueprint_Secret extends Blueprint_Input
	{

		public function __construct( $label, $ref = "", $model = "" )
		{
			parent::__construct($label, $ref, $model);
		}

		protected function getTagName()
		{
			return "secret";
		}
	}

	class Blueprint_TextArea extends Blueprint_Input
	{
		public function __construct( $label, $ref = "", $model = "" )
		{
			parent::__construct($label, $ref, $model);
			$this->appearance = "";
		}
		
		protected function getTagName()
		{
			return "textarea";
		}
	}
		
	class Blueprint_ImageList
	{
		protected $images;
		protected $moreAction;
		protected $moreLabel;
	    protected $size;
	    protected $fillStyle;
		protected $wrap;
		protected $class;

		function __construct(
			$imageTriggers = null, $moreLabel=null, $moreAction=null,
			$size = "medium", $fillStyle = null, $wrap = false)
		{
			if ( !isEmpty( $imageTriggers ) )
			{
				foreach( $imageTriggers as $image )
				{
					$this->addImageTrigger($image);
				}
			}
			
			if (!isEmpty($moreLabel))
			{
				$this->setMoreLink($moreLabel, $moreAction);	
			}

	        $this->size = $size;
	        $this->fillStyle = $fillStyle;
    	    $this->wrap = $wrap;

		}
	
		public function addImageTrigger( $image )
		{
			$this->images[] = $image;
		}
		
		public function setMoreLink( $labelText, $action )
		{
			if ((!isEmpty($labelText)) && is_string($labelText))
				$labelText = new Blueprint_Label( $labelText );
			$this->moreLabel = $labelText;
			if ((!isEmpty($action)) && is_string( $action ))
				$this->moreAction = new Blueprint_LoadAction( $action );
			else
				$this->moreAction = $action;
		}
	    
		public function setSize($size = "medium") {
    	    $this->size = $size;
    	}

    	public function setFillStyle($fillStyle) {
        	$this->fillStyle = $fillStyle;
    	}

    	public function setWrap($wrap = false) {
        	$this->wrap = $wrap;
    	}

	    public function emit($writer)
	    {
    	    $writer->startElement("image-list");

        	if (!isEmpty($this->size)) {
            	$writer->writeAttribute("size", $this->size);
        	}

        	if (!isEmpty($this->fillStyle)) {
            	$writer->writeAttribute("fill-style", $this->fillStyle);
        	}

        	$writer->writeAttribute("wrap", ($this->wrap ? "true" : "false"));
			
        	if (!empty($this->images))
        	{
            	foreach($this->images as $imageT)
            	{
                	$imageT->emit($writer);
            	}
        	}

        	if (!empty( $this->moreLabel))
        	{
            	$writer->startElement("more");
            	$this->moreLabel->emit($writer);

            	if (!empty($this->moreAction))
                	$this->moreAction->emit($writer);

            	$writer->endElement();  // more
        	}


        	$writer->endElement();  // image-list
    	}
	
	}
	
	class _ActionBase
	{
		protected $resource;
		protected $loadingText;
		protected $loadingTitle;
		protected $secure;
		
		public function __construct( $resource, $loadText = NULL, $isSecure = NULL )
		{
			$this->resource = $resource;
			$this->loadingText = $loadText;
			$this->secure = $isSecure;
		}

		public function setResource($resource) {
			$this->resource = $resource;
		}
		
		public function setLoadingText( $text )
		{
			$this->loadingText = $text;
		}	

		public function setLoadingTitle( $text )
		{
			$this->loadingTitle = $text;
				
		}
		
		public function setSecure( $isSecure = true )
		{
			$this->secure = $isSecure;
		}	
		
		public function emit( $writer )
		{
			$writer->startElement( $this->getTagName() );
			$this->emitAttributes($writer);
			if (!isEmpty($this->loadingText)) {
				$writer->writeElement( "loading-text", $this->loadingText );
			}	
			if (!isEmpty($this->loadingTitle)) {
				$writer->writeElement( "loading-title", $this->loadingTitle );
			}
			
			$writer->endElement();
		}

		protected function emitAttributes($writer) {
			$writer->writeAttribute( "event", "activate" );
			$writer->writeAttribute($this->getLinkName(), $this->resource);

			if ($this->secure !== null)
				$writer->writeAttribute( "secure", ($this->secure ? "true" : "false") );
		}
		
		protected function getTagName()
		{
			return "load";
		}
		
		protected function getLinkName()
		{
			return "resource";
		}
		
	}
	
	class Blueprint_LoadAction extends _ActionBase
	{
		protected function getTagName()
		{
			return "load";
		}
		
		protected function getLinkName()
		{
			return "resource";
		}
	}

	class Blueprint_LoadPageAction extends _ActionBase
	{
		protected $replace;
		protected $useCache = null;

    	public function __construct(
			$resource, $loadText = NULL, $isSecure = NULL, $replace = NULL)
    	{
        	parent::__construct($resource, $loadText, $isSecure);
        	$this->setReplace($replace);
    	}

	    public function setReplace($replace)
    	{
        	//only set replace with input value is either current or all
        	if (strcasecmp($replace, 'current')===0 || 
            	strcasecmp($replace, 'all')===0)
        	{
            	$this->replace = $replace;
        	}
        	else
        	{
            	$replace = ''; //empty($replace) will be true
        	}
        	$this->replace = $replace;
		}

		public function setUseCache($useCache = true) {
			$this->useCache = $useCache;
		}

		public function setPage($page) {
			$this->resource = $page;
		}
    
    	public function emit($writer)
    	{
        	$writer->startElement($this->getTagName());
        	$writer->writeAttribute('event', 'activate');

			$writer->writeAttribute($this->getLinkName(), $this->resource);
        
			if ($this->secure !== null)
        	{
				$writer->writeAttribute(
					'secure', ($this->secure ? 'true' : 'false')
            	);
			}

			if ($this->useCache !== null) {
				$writer->writeAttribute(
					'use-cache', ($this->useCache ? 'true' : 'false')
				);
			}
        
			if (!isEmpty($this->replace))
        	{
            	$writer->writeAttribute('replace', $this->replace);
        	}
        
			if (!isEmpty($this->loadingText)) {
				$writer->writeElement( "loading-text", $this->loadingText );
			}	
			if (!isEmpty($this->loadingTitle)) {
				$writer->writeElement( "loading-title", $this->loadingTitle );
			}

        	$writer->endElement();
    	}

		protected function getTagName()
		{
			return "load-page";
		}
		
		protected function getLinkName()
		{
			return "page";
		}
	}

	class Blueprint_YahooLogin
	{
		protected $event;
		protected $successPage;

		public function __construct($event = 'activate') {
			$this->event = $event;
		}
	    
		public function setEvent($event) {
			$this->event = $event;
		}

		public function setSuccessPage($successPage) {
			$this->successPage = $successPage;
		}

		public function emit( $writer )
		{
			$writer->startElement($this->getTagName());

			if (!isEmpty($this->event))
				$writer->writeAttribute("event", $this->event);

			if (!isEmpty($this->successPage))
				$writer->writeAttribute("success-page", $this->successPage);

			$writer->endElement();
		}

		protected function getTagName()
		{
			return 'yahoo-login';
		}
	}


	class Blueprint_Label extends _Container
	{
		public function __construct( $content )
		{
			$this->addContent( $content );
		}

		protected function isAllowed( $content )
		{
			return Blueprint_isInlineContent( $content );
		}
		
		protected function getTagName()
		{
			return "label";
		}
	}
	
	class Blueprint_ImageTrigger extends _ActionableElementBase 
	{
		protected $image;
		
		public function __construct( $image, $action = null)
		{
			if (is_string($image))
				$image = new Blueprint_Image($image);
			
			$this->image = $image;
			$this->addAction($action);
		}
		
		public function emit( $writer )
		{
			$writer->startElement( "image-trigger" );

			$this->image->emit( $writer );
			$this->emitActions($writer);
			
			$writer->endElement();
		}
	}
	
	class Blueprint_InlineTrigger extends _ActionableElementBase
	{
			protected $label;

			public function __construct($label, $action = null)
			{
				if (!($label instanceof Blueprint_Label))
					$label = new Blueprint_Label($label);
				$this->label = $label;

				if (!isEmpty($action))
					$this->addAction($action);
			}

			public function setLabel($label) {
				if (!($label instanceof Blueprint_Label))
					$label = new Blueprint_Label($label);

				$this->label = $label;
			}

			public function emit( $writer )
			{
				$writer->startElement( $this->getTagName() );

				// write the label
				if ( !isEmpty( $this->label ) )
					$this->label->emit( $writer );

				// write the action
				$this->emitActions( $writer );

				$writer->endElement();
			}

			public function isInlineContent() {
				return true;
			}

			protected function getTagName()
			{
				return "inline-trigger";
			}
	}
	
	class Blueprint_Rating
	{
		protected $stars;
		protected $reviews;
		protected $appearance;
		
		public function __construct( $stars = null, $reviews = null, $appearance = null)
		{
			$this->stars = $stars;
			$this->reviews = $reviews;
			$this->appearance = $appearance;
		}
		
		public function setAppearance( $a )
		{
			if ( ($a == "full") ||  ($a == "compact") || ($a == "minimal") )
				$this->appearance = $a;
		}
		
		public function emit( $writer )
		{
			$writer->startElement( "rating" );
			
			if ( !isEmpty( $this->appearance ) )
				$writer->writeAttribute( "appearance", $this->appearance );
			
			if ( !isEmpty( $this->stars ) )
			{
				$writer->startElement( "stars" );
				$writer->text( $this->stars );
				$writer->endElement();
			}

			if ( !isEmpty( $this->reviews ) )
			{
				$writer->startElement( "reviews" );
				$writer->text( $this->reviews );
				$writer->endElement();
			}
					
			$writer->endElement();
		}
	}
	
	class Blueprint_LinkSet
	{
		protected $triggers = array();	
		protected $class;
		protected $appearance;

		public function __construct($triggers = null) {
			if (!isEmpty($triggers))
			{
				if (!is_array($triggers))
					$triggers = array($triggers);

				$this->triggers = $triggers;
			}
		}


		public function addTrigger($trigger) {
			$this->triggers[] = $trigger;
		}

		public function setClass($class) {
			$this->class = $class;
		}

		public function setAppearance($appearance) {
			$this->appearance = $appearance;
		}

		public function emit($writer)
		{
			$writer->startElement($this->getTagName());

			if (!isEmpty($this->class)) {
				$writer->writeAttribute("class", $this->class);
			}

			if (!isEmpty($this->appearance)) {
				$writer->writeAttribute("appearance", $this->appearance);
			}

			if (!isEmpty($this->triggers))
			{
				foreach ($this->triggers as $trigger)
					$trigger->emit($writer);

			}

			$writer->endElement();
		}

		public function isControl() {
			return true;
		}

		protected function getTagName()
		{
			return "link-set";
		}
	}

	class Blueprint_PageInfo
	{
		protected $currentPage;
		protected $pageCount;

		public function __construct($currentPage, $pageCount) {
			$this->currentPage = $currentPage;
			$this->pageCount = $pageCount;
		}

		public function setCurrentPage($currentPage) {
			$this->currentPage = $currentPage;
		}

		public function setPageCount($pageCount) {
			$this->pageCount = $pageCount;
		}

		public function emit($writer) {
			$writer->startElement($this->getTagName());
			$writer->writeElement("current-page", $this->currentPage);
			$writer->writeElement("page-count", $this->pageCount);
			$writer->endElement();
		}

		protected function getTagName() {
			return "page-info";
		}
	}
	
	class Blueprint_PageNavigator
	{
		protected $pageInfo;
		protected $prev;
		protected $next;

		public function __construct($pageInfo = null)
		{
			$this->pageInfo = $pageInfo;	
		}

		public function setPageInfo($pageInfo = null) {
			$this->pageInfo = $pageInfo;
		}

		public function setPrev($label = null, $action = null) {
			if (empty($label) && empty($action)) {
				$this->prev = null;
			} else {
				$this->prev = new __NavLink("prev", $label, $action);
			}
		}

		public function setNext($label = null, $action = null) {
		if (empty($label) && empty($action)) {
				$this->next = null;
			} else {
				$this->next = new __NavLink("next", $label, $action);
			}
		}

		public function emit($writer) {
			$writer->startElement($this->getTagName());

			if (!isEmpty($this->pageInfo))
			{
				$this->pageInfo->emit($writer);	
			}

			if (!isEmpty($this->prev)) {
				$this->prev->emit($writer);
			}

			if (!isEmpty($this->next)) {
				$this->next->emit($writer);
			}

			$writer->endElement();
		}

		public function isControl() {
			return true;
		}

		protected function getTagName() {
			return "page-navigator";
		}
	}
	
	class __NavLink extends _ActionableElementBase
	{
		protected $tagName;
		protected $text;
		protected $action;
		
		public function __construct( $tagName, $text, $action )
		{
			$this->tagName = $tagName;
			$this->text = $text;
			
			$this->addAction($action);
		}
		
		public function emit( $writer )
		{
			$writer->startElement( $this->tagName );

			if (!isEmpty($this->text))
			{
				$writer->startElement( "label" );
				$writer->text( $this->text );
				$writer->endElement();  // label
			}
			
			$this->emitActions($writer);

			$writer->endElement();
		}
	}
	
	class Blueprint_NavigationBar
	{
		protected $back;
		protected $prev;
		protected $next;
		
		public function setBack( $text, $action )
		{
			$this->back = new __NavLink( "back", $text, $action );
		}
		
		public function setPrev( $text, $action )
		{
			$this->prev = new __NavLink( "prev", $text, $action );
		}

		public function setNext( $text, $action )
		{
			$this->next = new __NavLink( "next", $text, $action );
		}

		public function emit( $writer )
		{
			$writer->startElement( "navigation-bar" );
			
			if ( !isEmpty( $this->back ) )
				$this->back->emit( $writer );

			if ( !isEmpty( $this->prev ) )
				$this->prev->emit( $writer );			
			
			if ( !isEmpty( $this->next ) )
				$this->next->emit( $writer );
			else
				error_log( "Error: no 'next' element specified for navigation-bar", 0 );
				
			$writer->endElement();
		}
	}
	
	
	class Blueprint_Cell extends _Container
	{
		protected $emphasized;
		
		public function __construct( $content, $emphasized = null )
		{
			$this->addContent( $content );
			$this->emphasized = $emphasized;
		}

		protected function isAllowed( $content )
		{
			return( is_string( $content )
				|| $content instanceof Blueprint_Em
				|| $content instanceof Blueprint_Strong );
		}
		
		protected function getTagName()
		{
			return "cell";
		}
			
		protected function emitAdditional( $writer )
		{
			if (!isEmpty($this->emphasized))
				$writer->writeAttribute( "emphasized", $this->emphasized );
		}
	}
	
	class Blueprint_Row extends _ActionableElementBase
	{
		protected $emphasized;
		protected $title;
		protected $cells = array();
		
		public function __construct( $title, $cells=null, $emphasized = null )
		{
			$this->title = $title;
			$this->emphasized = $emphasized;
			
			if ( !is_array( $cells ) )
					$cells = array( $cells );
	
			foreach( $cells as $cell )
				$this->addCell( $cell );
		}
		
		public function addCell( $cell )
		{
			if ( is_array( $cell ) )
			{
				foreach( $cell as $c )
					$this->addCell( $c );
			}
			else
			{	
				if ( !isEmpty( $cell ) )
				{
					if ( is_string($cell) )
					{
						$cell = new Blueprint_Cell($cell);
					}
					$this->cells[] = $cell;
				}
			}
		}

		public function emit( $writer )
		{
			$writer->startElement( "row" );
			
			if (!isEmpty($this->emphasized))
				$writer->writeAttribute( "emphasized", $this->emphasized );

				
			$writer->startElement( "title" );
			$writer->text( $this->title );
			$writer->endElement();  // title
			
				
			if ( !isEmpty( $this->cells ) )
			{
				foreach( $this->cells as $cell )
				{
					$cell->emit( $writer );
				}
			}		

			$this->emitActions($writer);
			
			$writer->endElement();  // row
		}
	}
	
	class Blueprint_Table extends _ActionableElementBase
	{
		protected $bias;
		protected $dropOptional;
		
		protected $rows = array();
		protected $columns = array();
		
		public function __construct( $bias = null, $dropOptional = null, $columns = null, $rows = null )
		{
			$this->bias = $bias;
			$this->dropOptional = $dropOptional;
			
			$this->addColumn( $columns );
			$this->addRow( $rows );
		}
			
		public function addColumn( $column )
		{
			if ( is_array( $column ) )
			{
				foreach( $column as $c )
					$this->addColumn( $c );
			}
			else
			{		
				if ( !isEmpty( $column ) )
				{
					if ( is_string($column) )
					{
						$column = new Blueprint_Column($column);
					}
					$this->columns[] = $column;
				}
			}
		}		
		public function addRow( $row )
		{
			if ( is_array( $row ) )
			{
				foreach( $row as $r )
					$this->addRow( $r );
			}
			else
			{	
				if ( !isEmpty( $row ) )
				{
					if ( is_string($row) )
					{
						$row = new Blueprint_Row($row);
					}
					$this->rows[] = $row;
				}
			}
		}

		public function emit( $writer )
		{
			$writer->startElement( "table" );
	
			if (!isEmpty($this->bias))
				$writer->writeAttribute( "bias", $this->bias );
				
			if (!isEmpty($this->dropOptional))
				$writer->writeAttribute( "drop-optional", $this->dropOptional );

				
			$writer->startElement( "columns" );
			foreach( $this->columns as $column )
			{
				$column->emit( $writer );
			}
			$writer->endElement();  // columns	
					
			$writer->startElement( "rows" );
			foreach( $this->rows as $row )
			{
				$row->emit( $writer );
			}
			$writer->endElement();  // rows
			
							
			$writer->endElement();  // table
		}
	}
	
	
	class Blueprint_Column
	{
		protected $numericEmphasis;
		protected $title;
		protected $required;
		protected $align;
		
		public function __construct( $title, $required = null, $align = null, $numericEmphasis = null )
		{
			$this->title = $title;
			$this->required = $required;
			$this->align = $align;
			$this->numericEmphasis = $numericEmphasis;
		}

		public function emit( $writer )
		{
			$writer->startElement( "column" );
			
			if (!isEmpty($this->numericEmphasis))
				$writer->writeAttribute( "numeric-emphasis", $this->numericEmphasis );

			if (!isEmpty($this->required))
				$writer->writeAttribute( "required", $this->required );

			if (!isEmpty($this->align))
				$writer->writeAttribute( "align", $this->align );

				
			$writer->startElement( "title" );
			$writer->text( $this->title );
			$writer->endElement();  // title
			
			$writer->endElement();  // row
		}
	}
	
	class Blueprint_LocationChooser
	{
		protected $ref;
		protected $model;
		protected $setCurrent;
		protected $label;
		
		public function __construct( $label, $ref = null, $model = null, $setCurrent = null)
		{
			if (! ($label instanceof Blueprint_Label ) )
				$label = new Blueprint_Label( $label );
			
			$this->label = $label;
			
			$this->ref = $ref;
			$this->model = $model;
			$this->setCurrent = $setCurrent;
		}
		
					
		public function setSetCurrent( $setCurrent = true )
		{
			$this->setCurrent = $setCurrent;
		}		
		
		public function emit( $writer )
		{
			$writer->startElement( "location-chooser" );
			
			if ( !isEmpty( $this->ref ) )
				$writer->writeAttribute( "ref", $this->ref );

			if ( !isEmpty( $this->model ) )
				$writer->writeAttribute( "model", $this->model );

			if ( !isEmpty( $this->setCurrent ) )
				$writer->writeAttribute( "set-current", $this->setCurrent );
				
				
			// write the label
			if ( !isEmpty( $this->label ) )
			{
				$this->label->emit($writer);
			}

			$writer->endElement();
		}
	}
	
	class Blueprint_LocationElement
	{
	  protected $latitude;
	  protected $longitude;
	  protected $type;
	   protected $addressParts;
	   
	   public function __construct($latitude,$longitude,$addressParts=null, $type = null)
	   {
	   		$this->latitude = $latitude;
	   		$this->type = $type;
	   		$this->longitude = $longitude;
	   		if($addressParts != null)
	   		{
	   			$this->addressParts = $addressParts;
	   		}
	   }
	   
		protected function getTagName()
		{
			return "location";
		}
	   
	   public function emit( $writer )
	   {
	   		$writer->startElement( $this->getTagName() );
			$this->emitLocation($writer);
	   		$writer->endElement();	   			
	   }
	   
		public function emitLocation( $writer )
	    {
	    	if(!isEmpty($this->type)) {
            	$writer->writeAttribute( "type", $this->type);
        	} 
	   		
	   		$writer->startElement( "latitude");
	   	    $writer->text( $this->latitude );
	   		$writer->endElement();
	   		
	   		$writer->startElement( "longitude");
	   	    $writer->text( $this->longitude );
	   		$writer->endElement();
	   			
	   		if($this->addressParts != null)
	   			$this->addressParts->emit($writer);
		}
	   		
	   
	}
	
	class Blueprint_AddressParts
	{
		protected $street;
		protected $city;
		protected $state;
		protected $zipCode;
		protected $country;
		protected $name;
		
		protected $outputEmpty = true;
		
		public function __construct($name="",$street="",$city="",$state="",$zipCode = "",$country="")
		{
			$this->name = $name;
			$this->street = $street;
			$this->city = $city;
			$this->state = $state;
			$this->zipCode = $zipCode;
			$this->country = $country;
		}
		
		public function emit( $writer )
		{
			
			if($this->outputEmpty || !isEmpty($this->name))
			{
				
				$writer->startElement("name");
				$writer->text( $this->name );
				$writer->endElement();
				
			}
			if($this->outputEmpty || !isEmpty($this->street))
			{
				
				$writer->startElement("street");
				$writer->text( $this->street );
				$writer->endElement();
				
			}
			
			if($this->outputEmpty || !isEmpty($this->city))
			{
				
				$writer->startElement("city");
				$writer->text( $this->city );
				$writer->endElement();
				
			}
			
			if($this->outputEmpty || !isEmpty($this->state))
			{
		
				$writer->startElement("state");
				$writer->text( $this->state );
				$writer->endElement();
				
			}
			
			if($this->outputEmpty || !isEmpty($this->zipCode))
			{
				
				$writer->startElement("zip");
				$writer->text( $this->zipCode );
				$writer->endElement();
				
			}
			
			if($this->outputEmpty || !isEmpty($this->country))
			{
				
				$writer->startElement("country");
				$writer->text( $this->country );
				$writer->endElement();
				
			}
			
		}
		
	}
	
	class Blueprint_Center extends Blueprint_LocationElement
	{		
		protected function getTagName()
		{
			return "center";
		}
		
	}
	
	class Blueprint_Destination extends Blueprint_LocationElement
	{		
		protected function getTagName()
		{
			return "destination";
		}
		
	}
	
	class Blueprint_Origin extends Blueprint_LocationElement
	{		
		protected function getTagName()
		{
			return "origin";
		}
		
	}

	class Blueprint_MapRoute
	{
		protected $origin;
		protected $destination;
		
		public function __construct($origin, $destination)
		{
			$this->origin = $origin;
			$this->destination = $destination;
		}
		
		public function emit($writer)
		{
			$writer->startElement("map-route");
			if(!isEmpty($this->origin))	
				$this->origin->emit($writer);
			if(!isEmpty($this->destination))
				$this->destination->emit($writer);	
			$writer->endElement();
		}
	}
	


	// alais for Blueprint_Location for backwards compatability
	class Blueprint_LocationMap extends Blueprint_LocationElement
	{		
		protected function getTagName()
		{
			return "location";
		}		
	}

	class Blueprint_MapAspect
	{
		const ASPECT_SQUARE = 'square';
		const ASPECT_HALF_HEIGHT = 'half-height';

		public function __construct($aspect)
		{
			$this->aspect = $aspect;
		}

		public function emit($writer)
		{
			$writer->startElement('map-aspect');
			$writer->text($this->aspect);
			$writer->endElement();
		}
	}
	
	class Blueprint_ShowDrivingDirections
	{
		protected $origin;
		protected $destination;
		
		public function __construct($origin,$destination)
		{
			$this->origin = $origin;
			$this->destination = $destination;
		}
		
		public function emit($writer)
		{
			$writer->startElement("show-driving-directions");
			$writer->writeAttribute( "event", "activate" );
			if(!isEmpty($this->origin))	
				$this->origin->emit($writer);
			if(!isEmpty($this->destination))
				$this->destination->emit($writer);	
			$writer->endElement();
		}
	}
	
	
	class Blueprint_MapPoint
	{
		protected $location;
		protected $placard;
		
		public function __construct($location,$placard)
		{
			$this->location = $location;
			$this->placard = $placard;
		}
		
		public function emit( $writer )
		{
			$writer->startElement("map-point");
			if(!isEmpty($this->location))
				$this->location->emit($writer);
			if(!isEmpty($this->placard))
				$this->placard->emit($writer);	
			$writer->endElement();
			
		}
	}

	class Blueprint_DisplayUnits
	{
		const UNIT_ENGLISH = 'english';
		const UNIT_METRIC = 'metric';
		
		protected $unit;

		public function __construct($unit = self::UNIT_ENGLISH)
		{
			$this->unit = $unit;
		}

		public function setUnit($unit)
		{
			$this->unit = $unit;
		}

		public function emit($writer)
		{
			$writer->writeElement('display-units', $this->unit);
		}
	}
	
	class __MapBase
	{
		protected $center;
		protected $mapZoom; //can be integer 1 to 18
		protected $mapMode; //map/satellite/hybrid
		protected $mapShowTraffic; //true/false
		protected $mapPoints;
		protected $tagName;
		protected $mapRoute;
		protected $mapAspect;
		protected $displayUnits;
		
		public function __construct($center,$mapZoom,$mapMode,$tagName)
		{
			$this->center = $center;
			$this->mapZoom = $mapZoom;
			$this->mapMode = $mapMode;
			$this->tagName = $tagName;
		}
		
		public function addPoint($mapPoint)
		{
			$this->mapPoints[] = $mapPoint;
			
		}
		
		public function setMapShowTraffic($value=false)
		{
			$this->mapShowTraffic = $value;
		}

		public function setMapDirections($mapDirections)
		{
			$this->mapDirections = $mapDirections;
		}
		
		public function setMapAspect($mapAspect)
		{
			$this->mapAspect = $mapAspect;
		}

		public function setMapRoute($mapRoute)
		{
			$this->mapRoute = $mapRoute;
		}

		public function setDisplayUnits($displayUnits)
		{
			$this->displayUnits = $displayUnits;
		}

		public function emit($writer)
		{
			$writer->startElement($this->tagName);
			if($this->tagName == "show-map")
				$writer->writeAttribute( "event", "activate" );
				
			if(!isEmpty($this->center))//required
			{
				$this->center->emit($writer);
			}

			if (!isEmpty($this->mapRoute))
				$this->mapRoute->emit($writer);

			if (!isEmpty($this->mapAspect))
				$this->mapAspect->emit($writer);


			
			if(!isEmpty($this->mapZoom) )	
			{
				$writer->startElement("map-zoom");
				$writer->text($this->mapZoom);
				$writer->endElement();
			}

			if(!isEmpty($this->mapMode))//required	
			{
				if (strcasecmp($this->mapMode,"map")==0 ||
					strcasecmp($this->mapMode,"satellite")==0 ||
					strcasecmp($this->mapMode,"hybrid")==0 )
				{
					$writer->startElement("map-mode");
					$writer->text($this->mapMode);
					$writer->endElement();
				}
			}
			
			if(!isEmpty($this->mapShowTraffic))
			{
				$writer->startElement("map-showtraffic");
				
				if($this->mapShowTraffic)
					$writer->text("true");
				else
					$writer->text("false");
				
				$writer->endElement();
			}
						
			if(!isEmpty($this->mapPoints))
			{
				
				foreach($this->mapPoints as $mapPoint)
				{
					
					$mapPoint->emit( $writer );
				}
			}

			if (!isEmpty($this->displayUnits))
			{
				$this->displayUnits->emit($writer);
			}
			
			$writer->endElement();
			
			
		}
		
	}
	
	class Blueprint_Map extends __MapBase
	{
		public function __construct($center,$mapZoom,$mapMode)
		{
			parent::__construct($center,$mapZoom,$mapMode,"map");
		}
	}
	
	
	class Blueprint_ShowMap extends __MapBase
	{
		public function __construct($center,$mapZoom,$mapMode)
		{
			parent::__construct($center,$mapZoom,$mapMode,"show-map");		
		}
	}
	

	class Blueprint_DebugBlock extends Blueprint_Block
	{
		public function __construct( $var )
		{
			ob_start();
			var_dump($var);
			$text = ob_get_contents();
			ob_end_clean();
			
			parent::__construct( $text );			
		}
	}

	class Blueprint_PlayVideo {
		protected $event;
		protected $resource;
		protected $bitrate;
		
		const BITRATE_LOW = 'low';
		const BITRATE_MEDIUM = 'medium';
		const BITRATE_HIGH = 'high';

		public function __construct($resource, $event = "activate") {
			$this->resource = $resource;
			$this->event = $event;
		}

		public function setResource($resource) {
			$this->resource = $resource;
		}

		public function getResource() {
			return $this->resource;
		}

		public function setBitrate($bitrate) {
			$this->bitrate = $bitrate;
		}

		public function getBitrate() {
			return $this->bitrate;
		}
		
		public function setEvent($event) {
			$this->event = $event;
		}

		public function getEvent() {
			return $this->event;
		}

		public function emit($writer) {
			$writer->startElement('play-video');
			
			if (!isEmpty($this->event)) {
				$writer->writeAttribute('event', $this->event);
			}
			
			$writer->writeAttribute('resource', $this->resource);
			
			if (!isEmpty($this->bitrate)) {
				$writer->writeAttribute('bitrate', $this->bitrate);
			}

			$writer->endElement();
		}
	}

	/**
     * Classes for blueprint 1.x
	 */

	class Blueprint_DisplayAlert {
		protected $text;
		protected $event;
		protected $triggers = array();
		protected $severity = self::SEVERITY_NEUTRAL;
		
		const SEVERITY_NEUTRAL = 'neutral';
		const SEVERITY_POSITIVE = 'positive';
		const SEVERITY_NEGATIVE = 'negative';

		public function __construct($text, $event = 'activate' ) {
			$this->text = $text;
			$this->event = $event;
		}

		public function setText($text) {
			$this->text = $text;
		}

		public function getText() {
			return $this->text;
		}

		public function setEvent($event) {
			$this->event = $event;
		}

		public function getEvent() {
			return $this->event;
		}

		public function setSeverity($severity = self::SEVERITY_NEUTRAL) {
			$this->severity = $severity;
		}

		public function getSeverity() {
			return $this->severity;
		}
		
		public function addTrigger($trigger) {
			$this->triggers[] = $trigger;
		}

		public function setTriggers($triggers) {
			$this->triggers = $triggers;
		}

		public function getTriggers() {
			return $this->triggers;
		}

		public function emit($writer) {
			$writer->startElement('display-alert');
			if (!isEmpty($this->event)) {
				$writer->writeAttribute('event', $this->event);
			}
			
			if (!isEmpty($this->severity)) {
				$writer->writeAttribute('severity', $this->severity);
			}

			$writer->writeElement('text', $this->text);

			foreach ($this->triggers as $trigger) {
				$trigger->emit($writer);
			}

			$writer->endElement();
		}
	}	

	class Blueprint_DisplayPrompt {
		protected $text;
		protected $event;
		protected $triggers = array();

		public function __construct($text, $event = 'activate' ) {
			$this->text = $text;
			$this->event = $event;
		}

		public function setText($text) {
			$this->text = $text;
		}

		public function getText() {
			return $this->text;
		}

		public function setEvent($event) {
			$this->event = $event;
		}

		public function getEvent() {
			return $this->event;
		}
		
		public function addTrigger($trigger) {
			$this->triggers[] = $trigger;
		}

		public function setTriggers($triggers) {
			$this->triggers = $triggers;
		}

		public function getTriggers() {
			return $this->triggers;
		}

		public function emit($writer) {
			$writer->startElement('display-prompt');
			if (!isEmpty($this->event)) {
				$writer->writeAttribute('event', $this->event);
			}		
			$writer->writeElement('text', $this->text);

			foreach ($this->triggers as $trigger) {
				$trigger->emit($writer);
			}

			$writer->endElement();
		}
	}


	/**
     * End of classes for blueprint 1.x
     **/	 
	
	function Blueprint_isControl( $item )
	{
		if (method_exists($item,'isControl'))
			return $item->isControl();
		
		return( $item instanceof Blueprint_Trigger
			|| $item instanceof Blueprint_Select
			|| $item instanceof Blueprint_Select1
			|| $item instanceof Blueprint_Submit
			|| $item instanceof Blueprint_Placard
			|| $item instanceof Blueprint_Input
			|| $item instanceof Blueprint_Secret
			|| $item instanceof Blueprint_Textarea
			|| $item instanceof Blueprint_ImageList
			|| $item instanceof Blueprint_NavigationBar
			|| $item instanceof Blueprint_Map
			|| $item instanceof Blueprint_Table 	
			|| $item instanceof Blueprint_LocationChooser	
			);
	}
	
	function Blueprint_isInlineContent( $content )
	{
		if (method_exists($content,'isInlineContent'))
			return $content->isInlineContent();
		
		return( is_string( $content )
				|| $content instanceof Blueprint_Image
				|| $content instanceof Blueprint_Span
				|| $content instanceof Blueprint_Rating
				|| $content instanceof Blueprint_Em
				|| $content instanceof Blueprint_Strong
				|| $content instanceof Blueprint_Br
				);
	}

	function Blueprint_isInnerContent( $content )
	{
		return( $content instanceof Blueprint_Block
				|| Blueprint_isControl( $content ) );
	}


	function Blueprint_EmitGroup( $tagName, $items, $writer )
	{
		if ( !isEmpty( $items ) )
		{
			$writer->startElement( $tagName );
			
			foreach( $items as $item )
				$item->emit( $writer );
			
			$writer->endElement();
		}
	}

	function blueprintErrorHandler( $errno, $errstr, $errfile, $errline )
	{
		if ( $errno == E_USER_ERROR )
		{
			// Make sure we set the right content type
			header( "Content-Type: application/ x-ywidget+xml" );
			
			// Make sure not to cache. The Java client really cares.
			header( "Cache-Control: no-cache" );
			
			$writer = new XMLWriter();
			$writer->openMemory();
			$writer->setIndent( true );
			$writer->startDocument('1.0','UTF-8');
			
			$page = new Blueprint_Page();
			$page->setHeader(new Blueprint_SimpleHeader( "Error - ".htmlentities( $errno ) ) );
			$module = new Blueprint_Module();
			$page->addModule( $module );
			$module->addContent( new Blueprint_Block( htmlentities( $errstr ) ) );
			$module->addContent( new Blueprint_Block( "File: ".htmlentities( $errfile ) ) );
			$module->addContent( new Blueprint_Block( "Line: ".htmlentities( $errline ) ) );
			
			$page->Emit( $writer );
			print( $writer->outputMemory() );
			exit();
		}
	}
	
	function isEmpty($var)
	{
		if (($var === 0) || ($var === "0"))
			return false;
		else
			return (empty($var));
	}
	
	function blueprintSetErrorHandler()
	{
		set_error_handler( "blueprintErrorHandler" );
	}
?>
