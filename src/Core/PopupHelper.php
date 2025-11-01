<?php

namespace App\Core;

class PopupHelper
{
    /**
     * Genera el HTML base para el popup global.
     * Se puede llamar desde los archivos de layout principales (Panel.php).
     *
     * @param string $popupId El ID que tendrá el elemento principal del popup.
     * @return string El HTML del popup.
     */
    public static function render(string $popupId = 'globalPopup'): string
    {
        // Se usa el estilo de popup-overlay y popup-content
        // que ya existe en admin_panel.css y login.css
        return '
        <div id="' . htmlspecialchars($popupId) . '" class="popup-overlay">
            <div class="popup-content">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h5 id="' . htmlspecialchars($popupId) . 'Title" class="popup-title" style="margin: 0; font-size: 1.4rem; color: #1e293b;"></h5>
                    <button id="' . htmlspecialchars($popupId) . 'CloseBtnHeader" class="modal-close" style="background: none; border: none; font-size: 1.8rem; line-height: 1; color: #64748b; cursor: pointer; opacity: 0.7;">&times;</button>
                </div>
                <p id="' . htmlspecialchars($popupId) . 'Message" class="popup-message" style="font-size: 1.1em; color: #4b5563; margin-bottom: 25px;"></p>
                <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                 <button id="' . htmlspecialchars($popupId) . 'CloseBtn" class="popup-close-btn" style="padding: 10px 25px; border: none; color: white; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background-color 0.2s;"></button>
                </div>
            </div>
        </div>
        ';
    }
}