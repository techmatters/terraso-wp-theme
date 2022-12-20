document.addEventListener( 'DOMContentLoaded', addPlausibleTracking );

function addPlausibleTracking() {
	if ( ! document.body.classList.contains( 'guide-ilm-output' ) ) {
		return;
	}

	const output = document.querySelector( '[class="ilm-output-name"]' ).textContent;
	const element = document.querySelector( '[class="ilm-element-name"]' ).textContent;

	function trackGuideToolClick( event ) {
		const tool = this.textContent;
		plausible( 'ToolClick', {props: {output: output, element: element, tool: tool}} );
	}

	document.querySelectorAll( '.tool a' ).forEach( ( clickedTool ) => {
		clickedTool.onclick = null;
		clickedTool.addEventListener( 'click', trackGuideToolClick );
	} );
}
