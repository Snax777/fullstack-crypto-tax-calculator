
import styled from 'styled-components';
import NavBar from './components/NavBar/NavBar';
import Hero from './components/Hero/Hero';
import Footer from './components/Footer/Footer';

function App() {
  return (
    <Container>
      <NavBar />
      <Hero />
      <Footer />
    </Container>
  );
}

const Container = styled.div``;

export default App;
