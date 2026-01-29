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
    const handleKeyDown = (e) => e.key === "Escape" && onClose();
    document.addEventListener("keydown", handleKeyDown);
    return () => document.removeEventListener("keydown", handleKeyDown);
  }, [onClose]);

  if (!isOpen) return null;

  const canProcess = files.length || pastedData.trim() || walletAddresses.length;

  const addWalletAddress = () => {
    if (!walletInput) return;
    setWalletAddresses((prev) => [...prev, { blockchain: selectedBlockchain, address: walletInput }]);
    setWalletInput("");
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
                onChange={(e) => setFiles([...e.target.files])}
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
                    <li key={i}>{f.name}</li>
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
              <div style={{ borderLeft: "5px solid #53aa4b", background: "#f3f3f3", padding: "0 0.5rem",borderRadius: "4px", marginBottom: "1rem" }}>
                Connect Your Wallet Addresses Add one or multiple wallet addresses to automatically fetch your
                transaction history from the blockchain.
              </div>
              <BlockchainGrid>
                {["Bitcoin", "Ethereum", "Polygon", "Solana"].map((chain) => (
                  <BlockchainTile
                    key={chain}
                    active={selectedBlockchain === chain}
                    onClick={() => setSelectedBlockchain(chain)}
                  >
                    <span style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                      {blockchainIcons[chain]}
                      {chain}
                    </span>
                  </BlockchainTile>
                ))}
              </BlockchainGrid>

              <AddressRow>
                <input
                  disabled={!selectedBlockchain}
                  value={walletInput}
                  onChange={(e) => setWalletInput(e.target.value)}
                  placeholder="Enter wallet address"
                />
                <button disabled={!walletInput || !selectedBlockchain} onClick={addWalletAddress}>
                  + Add
                </button>
              </AddressRow>

              {walletAddresses.length > 0 && (
                <FileList>
                  {walletAddresses.map((w, i) => (
                    <li key={i}>
                      {w.blockchain}: {w.address}
                    </li>
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
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  height: 90vh;
`;

const Backdrop = styled.div`
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
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

const Subtitle = styled.p`
  font-size: 0.875rem;
  color: #6b7280;
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
  border-bottom: ${({ active }) => (active ? "#0097A7" : "#0097A700")} 3px solid;
  color: ${({ active }) => (active ? "#0097A7" : "#000")};
  cursor: pointer;
  display: flex;
  align-items: center;
`;

const UploadZone = styled.div`
  border: 2px dashed #cbd5f5;
  border-radius: 0.75rem;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  background: #f8fafc;
`;

const UploadIcon = styled.div`
  font-size: 2rem;
  margin-bottom: 0.5rem;
`;

const FileList = styled.ul`
  margin-top: 1rem;
  font-size: 0.875rem;
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

const BlockchainGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 0.75rem;
  margin-bottom: 1rem;
`;

const BlockchainTile = styled.button`
  padding: 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
  background: ${({ active }) => (active ? " #0097A7" : "#f9fafb")};
  color: ${({ active }) => (active ? "#fff" : "#000")};
`;

const AddressRow = styled.div`
  display: flex;
  gap: 0.5rem;

  input {
    flex: 1;
    padding: 0.5rem;
  }

  button {
    background: #10b981;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    &:disabled {
      background: #6ee7b7;
      cursor: not-allowed;
    }
  }
`;

const FileFormats = styled.div`
  display: flex;
  margin-bottom: 0.5rem;
`;
const FileFormat = styled.div`
  display: flex;
  background: #f3f3f3;
  border-radius: 0.5rem;
  color: #757575;
  padding: 0.1rem 1rem;
  margin-right: 0.75rem;
  align-items: center;
  gap: 0.5rem;
  border-radius: 1.5rem;
`;

const Footer = styled.div`
  padding: 1rem 2rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
`;

const Secondary = styled.button`
  background: #f3f4f6;
  border: none;
  padding: 0.5rem 1.25rem;
  border-radius: 0.5rem;
  cursor: pointer;
  &:hover {
    background: #ff8989;
    color: #fff;
  }
`;

const Primary = styled.button`
  background: #0ea5e9;
  color: #fff;
  border: none;
  padding: 0.5rem 1.25rem;
  border-radius: 0.5rem;
  cursor: pointer;
  &:disabled {
    background: #93c5fd;
    cursor: not-allowed;
  }
`;
