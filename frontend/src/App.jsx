import { useState } from 'react';
import styled from 'styled-components';
import NavBar from './components/NavBar/NavBar';
import Hero from './components/Hero/Hero';

function App() {
  return (
    <Container>
      <NavBar />
      <Hero />
    </Container>
  );
}

const Container = styled.div``;

export default App;
