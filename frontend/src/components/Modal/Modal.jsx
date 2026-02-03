import { useEffect, useState } from "react";
import styled, { keyframes } from "styled-components";
import {
  FaPaste,
  FaFile,
  FaLink,
  FaUpload,
  FaBitcoin,
  FaEthereum,
  FaCalculator,
  FaFileCsv,
  FaFileExcel,
  FaFileAlt,
} from "react-icons/fa";
import { SiPolygon, SiSolana } from "react-icons/si";

const TABS = {
  FILE: "file",
  PASTE: "paste",
  WALLET: "wallet",
};

const blockchainIcons = {
  Bitcoin: <FaBitcoin />,
  Ethereum: <FaEthereum />,
  Polygon: <SiPolygon />,
  Solana: <SiSolana />,
};

const fileFormats = [
  { name: "CSV", icon: <FaFileCsv /> },
  { name: "Excel", icon: <FaFileExcel /> },
  { name: "Text", icon: <FaFileAlt /> },
];

export default function ImportModal({ isOpen, onClose }) {
  const [activeTab, setActiveTab] = useState(TABS.FILE);
  const [files, setFiles] = useState([]);
  const [pastedData, setPastedData] = useState("");
  const [walletAddresses, setWalletAddresses] = useState([]);
  const [selectedBlockchain, setSelectedBlockchain] = useState(null);
  const [walletInput, setWalletInput] = useState("");

  useEffect(() => {
    const handleKeyDown = (e) => {
      if (e.key === "Escape") onClose();
    };
    document.addEventListener("keydown", handleKeyDown);
    return () => document.removeEventListener("keydown", handleKeyDown);
  }, [onClose]);

  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "auto";
    }

    return () => {
      document.body.style.overflow = "auto";
    };
  }, [isOpen]);

  if (!isOpen) return null;

  const canProcess = files.length > 0 || pastedData.trim().length > 0 || walletAddresses.length > 0;

  const addWalletAddress = () => {
    if (!walletInput) return;
    setWalletAddresses((prev) => [...prev, { blockchain: selectedBlockchain, address: walletInput.trim() }]);
    setWalletInput("");
  };

  const handleRemoveFile = (index) => {
    setFiles((prev) => prev.filter((_, i) => i !== index));
  };

  const handleFileChange = (e) => {
    const newFiles = Array.from(e.target.files);
    setFiles((prev) => [...prev, ...newFiles]);
  };

  const handleRemoveWallet = (index) => {
    setWalletAddresses((prev) => prev.filter((_, i) => i !== index));
  };

  // Optionally define a placeholder selectBlockchain function if needed.
  const selectBlockchain = (chainKey, chainName) => {
    // Placeholder function: you can add logic to handle blockchain selection,
    // such as fetching data or initializing something.
    console.log(`Blockchain selected: ${chainName} (${chainKey})`);
  };

  return (
    <Modal>
      <Backdrop onClick={onClose} />
      <Content onClick={(e) => e.stopPropagation()}>
        <Header>
          <div>
            <h2>Import Your Transaction Data</h2>
          </div>
          <Close onClick={onClose}>✕</Close>
        </Header>

        <Body>
          <Tabs>
            {Object.values(TABS).map((tab) => (
              <Tab key={tab} active={activeTab === tab} onClick={() => setActiveTab(tab)}>
                {tab === "file" && (
                  <>
                    <div style={{ fontSize: "1.2rem", paddingRight: "0.5rem" }}>
                      <FaFile />
                    </div>
                    Upload File
                  </>
                )}
                {tab === "paste" && (
                  <>
                    <div style={{ fontSize: "1.2rem", paddingRight: "0.5rem" }}>
                      <FaPaste />
                    </div>
                    Paste Data
                  </>
                )}
                {tab === "wallet" && (
                  <>
                    <div style={{ fontSize: "1.2rem", paddingRight: "0.5rem" }}>
                      <FaLink />
                    </div>
                    Connect Wallet
                  </>
                )}
              </Tab>
            ))}
          </Tabs>

          {activeTab === TABS.FILE && (
            <>
              <label>Supported Formats:</label>
              <FileFormats>
                {fileFormats.map((format) => (
                  <FileFormat key={format.name}>
                    {format.icon}
                    {format.name}
                  </FileFormat>
                ))}
              </FileFormats>

              <input
                type="file"
                hidden
                multiple
                accept=".csv,.xlsx,.xls,.txt"
                id="file-input"
                onChange={handleFileChange}
              />
              <UploadZone onClick={() => document.getElementById("file-input").click()}>
                <UploadIcon>
                  <FaUpload />
                </UploadIcon>
                <strong>Drag & drop files</strong>
                <span>or click to browse</span>
              </UploadZone>

              {files.length > 0 && (
                <FileList>
                  {files.map((f, i) => (
                    <FileItem key={i}>
                      <div style={{ display: "flex", alignItems: "center", gap: "0.75rem" }}>
                        <FaFile />
                        <div>
                          <p style={{ margin: 0, fontWeight: 600 }}>{f.name}</p>
                          <small style={{ fontSize: "0.75rem", color: "var(--gray-500)" }}>
                            {(f.size / 1024).toFixed(2)} KB
                          </small>
                        </div>
                      </div>

                      <div style={{ display: "flex", alignItems: "center", gap: "0.5rem" }}>
                        <FileStatus ready>{`✓ Ready to process`}</FileStatus>
                        <RemoveButton onClick={() => handleRemoveFile(i)}>✕</RemoveButton>
                      </div>
                    </FileItem>
                  ))}
                </FileList>
              )}
            </>
          )}

          {activeTab === TABS.PASTE && (
            <>
              <label>Paste Your Transaction Data</label>
              <Textarea
                value={pastedData}
                onChange={(e) => setPastedData(e.target.value)}
                placeholder="Date | Type | Coin | Quantity | Price | Fee"
              />
            </>
          )}

          {activeTab === TABS.WALLET && (
            <>
              <div
                style={{
                  background: "var(--gray-50)",
                  borderLeft: "4px solid var(--primary-teal)",
                  padding: "1rem 1.5rem",
                  borderRadius: "var(--radius-md)",
                  color: "var(--gray-600)",
                  marginBottom: "1.5rem",
                }}
              >
                Connect Your Wallet Addresses. Add one or multiple wallet addresses to automatically fetch your
                transaction history from the blockchain.
              </div>
              <BlockchainTiles>
                {["Bitcoin", "Ethereum", "Polygon", "Solana"].map((chain) => (
                  <BlockchainTile
                    key={chain}
                    selected={selectedBlockchain === chain}
                    onClick={() => {
                      setSelectedBlockchain(chain);
                      selectBlockchain(chain.toLowerCase(), chain);
                    }}
                  >
                    <BlockchainLogo>{blockchainIcons[chain]}</BlockchainLogo>
                    <BlockchainName>{chain}</BlockchainName>
                    <BlockchainSymbol>
                      {chain === "Bitcoin"
                        ? "BTC"
                        : chain === "Ethereum"
                          ? "ETH"
                          : chain === "Polygon"
                            ? "MATIC"
                            : "SOL"}
                    </BlockchainSymbol>
                  </BlockchainTile>
                ))}
              </BlockchainTiles>

              <AddressRow>
                <input
                  disabled={!selectedBlockchain}
                  value={walletInput}
                  onChange={(e) => setWalletInput(e.target.value)}
                  placeholder="Enter wallet address"
                />
                <button disabled={!walletInput.trim() || !selectedBlockchain} onClick={addWalletAddress}>
                  + Add Address
                </button>
              </AddressRow>

              {walletAddresses.length > 0 && (
                <FileList as="ul">
                  {walletAddresses.map((w, i) => (
                    <WalletItem key={i}>
                      <div>
                        <strong>{w.blockchain}:</strong> {w.address}
                      </div>
                      <RemoveButton onClick={() => handleRemoveWallet(i)}>✕</RemoveButton>
                    </WalletItem>
                  ))}
                </FileList>
              )}
            </>
          )}
        </Body>

        <Footer>
          <Secondary onClick={onClose}>Cancel</Secondary>
          <Primary disabled={!canProcess}>
            <FaCalculator /> Process & Calculate
          </Primary>
        </Footer>
      </Content>
    </Modal>
  );
}

const slideIn = keyframes`
  from { opacity: 0; transform: translateY(10px) scale(.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
`;

const Modal = styled.div`
  display: flex;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  max-height: 90vh;
  z-index: 1000;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  overflow-y: auto;
`;

const Backdrop = styled.div`
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  height: 100vh;
`;

const Content = styled.div`
  background: #fff;
  max-width: 900px;
  width: 100%;
  border-radius: 1rem;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: ${slideIn} 0.25s ease-out;
  z-index: 2;
`;

const Header = styled.div`
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
`;

const Close = styled.button`
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
`;

const Body = styled.div`
  padding: 1.5rem 2rem;
  overflow-y: auto;
  flex: 1;
`;

const Tabs = styled.div`
  display: flex;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid #e5e7eb;
`;

const Tab = styled.button`
  flex: 1;
  border: none;
  background: none;
  padding: 1rem;
  margin: 0rem;
  border-bottom: ${({ active }) => (active ? "var(--primary-teal)" : "#ff262600")} 3px solid;
  color: ${({ active }) => (active ? "var(--primary-teal)" : "var(--gray-600)")};
  cursor: pointer;
  display: flex;
  align-items: center;
  font-size: 0.9375rem;
  font-weight: 600;
  &:hover {
    color: var(--primary-teal);
    background: var(--gray-50);
  }
`;

const UploadZone = styled.div`
  border: 3px dashed var(--gray-300);
  border-radius: 0.75rem;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  background: var(--gray-50);
  transition: all 0.2s ease;

  &:hover,
  .drag-over {
    border-color: var(--primary-teal);
    background: var(--primary-teal-light);
    transform: scale(1.01);
  }
`;

const UploadIcon = styled.div`
  font-size: 2rem;
  margin-bottom: 0.5rem;
`;

const FileList = styled.ul`
  margin-top: 1rem;
  list-style-type: none;
  padding: 0;
  font-size: 0.875rem;
`;

const FileItem = styled.li`
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border: 1px solid var(--gray-200);
  border-radius: var(--radius-md);
  margin-bottom: 0.5rem;
  background: var(--gray-50);
`;

const WalletItem = styled.li`
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border: 1px solid var(--gray-200);
  border-radius: var(--radius-md);
  margin-bottom: 0.5rem;
  background: var(--gray-50);
`;

const FileStatus = styled.div`
  font-weight: 600;
  color: var(--success);
  font-size: 0.875rem;
  white-space: nowrap;
`;

const RemoveButton = styled.button`
  background: transparent;
  border: none;
  color: var(--gray-400);
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: var(--radius-sm);
  transition: all 0.2s;

  &:hover {
    color: var(--error);
    background: var(--gray-100);
  }
`;

const Textarea = styled.textarea`
  width: 100%;
  min-height: 180px;
  margin-top: 0.5rem;
  padding: 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid #d1d5db;
  font-family: monospace;
`;

const BlockchainTiles = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
`;

const BlockchainTile = styled.div`
  background: var(--white);
  border: 3px solid var(--gray-200);
  border-radius: var(--radius-lg);
  padding: 1.5rem 1rem;
  text-align: center;
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;

  &:hover {
    border-color: var(--primary-teal);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }

  ${({ selected }) =>
    selected &&
    `
    border-color: var(--primary-teal);
    background: var(--primary-teal-light);
    box-shadow: var(--shadow-md);

    &::after {
      content: "✓";
      position: absolute;
      top: 0.5rem;
      right: 0.5rem;
      background: var(--primary-teal);
      color: var(--white);
      width: 24px;
      height: 24px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.875rem;
      font-weight: 700;
    }
  `}
`;

const BlockchainLogo = styled.div`
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
`;

const BlockchainName = styled.div`
  font-weight: 600;
  color: var(--gray-900);
  font-size: 0.9375rem;
  margin-bottom: 0.25rem;
`;

const BlockchainSymbol = styled.div`
  font-size: 0.75rem;
  color: var(--gray-500);
  font-weight: 600;
`;

const AddressRow = styled.div`
  display: flex;
  gap: 0.5rem;

  input {
    flex: 1;
    padding: 0.875rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    font-size: 0.875rem;
    font-family: "Monaco", "Courier New", monospace;
    transition: all 0.2s;
    background: var(--gray-50);

    &:focus {
      outline: none;
      border-color: var(--primary-teal);
      background: var(--white);
      box-shadow: 0 0 0 3px var(--primary-teal-light);
    }

    &:disabled {
      background: var(--gray-100);
      cursor: not-allowed;
    }
  }

  button {
    background: var(--primary-teal);
    color: var(--white);
    border: none;
    padding: 0.875rem 1.5rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 0.5rem;

    &:hover {
      background: var(--primary-teal-dark);
      transform: translateY(-1px);
      box-shadow: var(--shadow-md);
    }

    &:disabled {
      background: var(--gray-300);
      color: var(--gray-500);
      cursor: not-allowed;
      box-shadow: none;
      transform: none;
    }
  }
`;

const FileFormats = styled.div`
  display: flex;
  flex-wrap: wrap;
  margin-bottom: 0.5rem;
  gap: 0.5rem;
`;

const FileFormat = styled.div`
  background: var(--white);
  border: 2px solid var(--gray-200);
  border-radius: 20px;
  padding: 0.5rem 1rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
  transition: all 0.2s;

  &:hover {
    border-color: var(--primary-teal);
    background: var(--primary-teal-light);
    color: var(--primary-teal-dark);
  }
`;

const Footer = styled.div`
  padding: 1rem 2rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
`;

const Secondary = styled.button`
  background: var(--white);
  color: var(--primary-teal);
  border: 2px solid var(--primary-teal);
  padding: 0.5rem 1.25rem;
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background: var(--primary-teal);
    color: var(--white);
  }
`;

const Primary = styled.button`
  background: linear-gradient(135deg, var(--accent-coral) 0%, #ff5252 100%);
  color: white;
  border: none;
  padding: 1rem 2.5rem;
  border-radius: var(--radius-lg);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: var(--shadow-md);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;

  &:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }

  &:active {
    transform: translateY(0);
  }

  &:disabled {
    background: var(--gray-300);
    color: var(--gray-500);
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
  }
`;
