import React from 'react'

const ImportButton = () => {
  return (
    <div class='import-section'>
      <button class='btn-import-large' onclick='openImportModal()'>
        <div class='import-icon'>📥</div>
        <div>
          <div class='import-title'>Import Transaction Data</div>
          <div class='import-subtitle'>
            Upload files, paste data, or connect wallets to get started
          </div>
        </div>
      </button>
    </div>
  );
}

export default ImportButton